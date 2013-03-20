<?php
// Slim
require 'vendor/autoload.php';

// Config et fonctions utiles
require "config.php";

$MADMIN = new SoapClient($CONF['soap_url']);

require "inc/functions.php";

// Permet à plusieurs instances de casper de tourner sur le même hôte
// (et aussi de ne pas se faire piquer des cookies)
$sessionPath = parse_url($CONF['casper_url'], PHP_URL_PATH);
session_set_cookie_params(0, $sessionPath);

session_start();


$app = new \Slim\Slim();

$app->get('/', 'auth', function() use($app, $CONF, $MADMIN) {
    $app->render('header.php', array(
        "title" => $CONF["title"]
    ));
    $app->render('main.php', array(
        "userDetails" => $MADMIN->getUserDetails(),
        "max_reload" => $MADMIN->getMaxReload(),
        "min_reload" => $MADMIN->getMinReload(),
        "histo" => get_histo($MADMIN),
        "isBlocked" => $MADMIN->isBlocked(),
        "default_reload_value" => 10.00
    ));
    $app->render('footer.php');
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
		echo $MADMIN->reload($amount, $CONF['casper_url'].'postreload');
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

$app->get('/postreload', function() use ($app) {
    switch($_GET['paybox']) {
        case 'erreur':
            $app->flash('reload_erreur', 'Erreur Paybox n°'.$_GET['NUMERR']);
        break;
        case 'annule':
            $app->flash('reload_erreur', 'Vous avez annulé le rechargement.');
        break;
        case 'refuse':
            $app->flash('reload_erreur', 'La transaction a été refusée.');
        break;
        case 'effectue':
            $app->flash('reload_ok', 'Votre compte à été rechargé.');
        break;
    }
});

$app->run();