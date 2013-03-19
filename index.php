<?php
// Slim
require 'vendor/autoload.php';

// Config et fonctions utiles
require "config.php";

$MADMIN = new SoapClient($CONF['soap_url']);

require "inc/functions.php";
require "inc/auth.php";

// split auth en middleware

$app = new \Slim\Slim();

$app->get('/', function() use($app, $CONF, $MADMIN) {
    /*if(isset($_GET["paybox"])) {
    	if(isset($_GET["trans"])) {
    		if($_GET['paybox'] == 'erreur') {
    			header("Location: ".$CONF['casper_url']."?paybox=".$_GET["paybox"]."&NUMERR=".$_GET['NUMERR']);
    			exit();
    		} else {
    			header("Location: ".$CONF['casper_url']."?paybox=".$_GET["paybox"]);
    			exit();
    		}
    	}
	
    	if($_GET['paybox'] == 'erreur') { 
    		$num_err=$_GET['NUMERR'];
    		$error_reload = "<p>Erreur PAYBOX n°$num_err</p>";
    	} else if($_GET['paybox'] == 'annule') { // On a une annulation
    		$error_reload = "<p>Vous avez annulé le rechargement.</p>";	
    	} else if($_GET['paybox'] == 'refuse') { // la transaction a ete refuse
    		$error_reload = "<p>Transaction refusée</p>";
    	} else if($_GET['paybox'] == 'effectue') { // a priori ça a l'air bon
    		$success_reload = "<p>Votre compte à été rechargé.</p>";
    	}
    }*/
    
    $app->render('header.php', array("CONF" => $CONF));
    $app->render('main.php', array(
        "CONF" => $CONF,
        "userDetails" => $MADMIN->getUserDetails(),
        "max_reload" => $MADMIN->getMaxReload(),
        "min_reload" => $MADMIN->getMinReload(),
        "histo" => get_histo($MADMIN),
        "isBlocked" => $MADMIN->isBlocked(),
        "default_reload_value" => 10.00
    ));
    $app->render('footer.php', array("CONF" => $CONF));
})->name('home');

$app->get('/block', function() use ($app, $MADMIN) {
    $MADMIN->blockMe();
    $app->response()->redirect($app->urlFor('home'));
});

$app->get('/unblock', function() use ($app, $MADMIN) {
    $MADMIN->deblock();
    $app->response()->redirect($app->urlFor('home'));
});

$app->get('/ajax', function() use ($MADMIN) {
    echo $MADMIN->getRpcUser($_GET["search"]);
});

$app->get('/logout', function() use ($MADMIN, $CONF) {
    session_destroy();
    header("Location: ".$MADMIN->getCasUrl()."/logout?url=".$CONF['casper_url']);
});

$app->post('/reload', function() use ($app, $MADMIN, $CONF) {
	if(empty($_POST["montant"])) {
        $app->flash('error_reload', "Saisissez un montant");
        $app->response()->redirect($app->urlFor('home'));
    }

    $amount = parse_user_amount($_POST['montant']);
        
	$can = $MADMIN->canReload($amount);
	if($can == 1) {
		// On peut recharger
		echo $MADMIN->reload($amount, $CONF['casper_url']);
		$app->stop();
	} else {
		$erreur = str_getcsv(substr($MADMIN->getErrorDetail($can), 0, -2));
        $app->flash('reload_erreur', '<p>Erreur n°'.$erreur[0].' : <b>'.$erreur[1].'</b></p><p>'.$erreur[2].'</p>');
        $app->flash('reload_value', $amount/100);
        
        $app->response()->redirect($app->urlFor('home'));
	}
});

$app->post('/virement', function() use ($app, $MADMIN, $CONF) {
    $montant = parse_user_amount($_POST['montant']);
    
	$code = $MADMIN->transfert($montant, $_POST["userId"]);

    // Si le virement a échoué
    if($code != 1){
        $erreur = str_getcsv(substr($MADMIN->getErrorDetail($code), 0, -2));
        $app->flash('virement_erreur', '<p>Erreur n°'.$erreur[0].' : <b>'.$erreur[1].'</b></p><p>'.$erreur[2].'</p>');
        $app->flash('virement_value', $montant/100);
	}
    else {
        $app->flash('virement_ok', 'Le virement de '.format_number($montant).' € à réussi.');
    }
    
    $app->response()->redirect($app->urlFor('home'));
});

$app->run();