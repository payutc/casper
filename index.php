<?php
// Slim
require 'vendor/autoload.php';

// Config et fonctions utiles
require "config.php";
require "SoapCookies.php";
$MADMIN = new SoapClient($CONF['soap_url']);

require "inc/functions.php";

// Permet à plusieurs instances de casper de tourner sur le même hôte
// (et aussi de ne pas se faire piquer des cookies)
$sessionPath = parse_url($CONF['casper_url'], PHP_URL_PATH);
session_set_cookie_params(0, $sessionPath);

session_start();

$app = new \Slim\Slim();
$app->add(new \Payutc\SoapCookies);

$app->get('/', 'userLoggedIn', function() use($app, $CONF, $MADMIN) {
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

$app->get('/block', 'userLoggedIn', function() use ($app, $MADMIN) {
    $MADMIN->blockMe();
    $app->response()->redirect($app->urlFor('home'));
});

$app->get('/unblock', 'userLoggedIn', function() use ($app, $MADMIN) {
    $MADMIN->deblock();
    $app->response()->redirect($app->urlFor('home'));
});

$app->get('/ajax', 'userLoggedIn', function() use ($MADMIN) {
    echo $MADMIN->getRpcUser($_GET["search"]);
});

$app->post('/reload', 'userLoggedIn', function() use ($app, $MADMIN, $CONF) {
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

$app->post('/virement', 'userLoggedIn', function() use ($app, $MADMIN, $CONF) {
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

$app->get('/postreload', 'userLoggedIn', function() use ($app) {
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

$app->get('/register', function() use ($app, $CONF) {
    $app->render('header.php', array(
        "title" => $CONF["title"]
    ));
    $app->render('register.php', array(
    ));
    $app->render('footer.php', array("CONF" => $CONF));
})->name('register');

$app->post('/register', function() use ($app, $MADMIN) {
    $result = $MADMIN->register();
    
    if(isset($result["success"])) {
        $app->redirect($app->urlFor('home'));
    } else {
        if(isset($result["error_msg"])) {
            $app->flash('register_erreur', $result["error_msg"]);
        } else {
            $app->flash('register_erreur', "Échec de la création du compte.");
        }
        $app->redirect($app->urlFor('register'));
    }
});

// --- CAS
$app->get('/login', function() use ($app, $CONF, $MADMIN) {
    // Si pas de ticket, c'est une invitation à se connecter
    if(empty($_GET["ticket"])) {
        // On jette les cookies actuels
        unset($_SESSION['cookies']);
        
        // Redirection vers le CAS
        $app->redirect($MADMIN->getCasUrl()."/login?service=".$CONF['casper_url'].'login');
    } else {
        // Connexion au serveur avec le ticket
        $result = $MADMIN->loginCas($_GET["ticket"], $CONF['casper_url'].'login');

        if(isset($result["success"])) {
            // On stocke les cookies (SoapCookies les rechargera après)
            $_SESSION['cookies'] = $MADMIN->_cookies;
            
            // Go vers la page d'accueil
            $app->redirect($app->urlFor('home'));
        } else if(isset($result["error"])) {
            // Si non inscrit, création de compte
            if($result["error"] == 405) {
                // On doit garder les cookies car le serveur garde le login de son côté
                $_SESSION['cookies'] = $MADMIN->_cookies;
                
                // Redirection vers la charte
                $app->redirect($app->urlFor('register'));
            } else {
                if(isset($result["error_msg"])) {
                    // Si on a un message, on l'affiche
                    $login_erreur = $result["error_msg"];
                } else {
                    // Sinon, on essaie de récupérer le message correspondant à ce code d'erreur
                    $erreur = str_getcsv(substr($MADMIN->getErrorDetail($result["error"]), 0, -2));
                    $login_erreur = '<p>Erreur n°'.$erreur[0].' : <b>'.$erreur[1].'</b></p><p>'.$erreur[2].'</p>';
                }
                
                // Affichage d'une page avec juste l'erreur
                $app->render('header.php', array("title" => $CONF["title"]));                
                $app->render('error.php', array('login_erreur' => $login_erreur));
                $app->render('footer.php');
            }
        }        
    }
})->name('login');


$app->run();