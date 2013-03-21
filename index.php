<?php
// Slim
require 'vendor/autoload.php';

// Config et fonctions utiles
require "config.php";
require "inc/functions.php";
require "inc/SoapCookies.php";

// Restriction des cookies au chemin de casper et démarrage de la session
$sessionPath = parse_url($CONF['casper_url'], PHP_URL_PATH);
session_set_cookie_params(0, $sessionPath);
session_start();

// Connexion SOAP
$MADMIN = new SoapClient($CONF['soap_url']);

// Lancement de Slim
$app = new \Slim\Slim();

// Ce Middleware fait persister les cookies du SOAP
$app->add(new \Payutc\SoapCookies);

// --- Coeur de casper

// Page principale
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

// Blocage du compte
$app->get('/block', 'userLoggedIn', function() use ($app, $MADMIN) {
    $MADMIN->blockMe();
    $app->response()->redirect($app->urlFor('home'));
});

// Déblocage du compte
$app->get('/unblock', 'userLoggedIn', function() use ($app, $MADMIN) {
    $MADMIN->deblock();
    $app->response()->redirect($app->urlFor('home'));
});

// Autocomplete du virement
$app->get('/ajax', 'userLoggedIn', function() use ($MADMIN) {
    echo json_encode($MADMIN->userAutocomplete($_GET["search"]));
});

// Départ vers le rechargement
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

// Retour du rechargement
$app->get('/postreload', 'userLoggedIn', function() use ($app) {
    // Génération du message à afficher
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
    
    // Retour vers la page d'accueil
    $app->redirect($app->urlFor('home'));
});

// Virement à un ami
$app->post('/virement', 'userLoggedIn', function() use ($app, $MADMIN, $CONF) {
    // Récupèration du montant en cents
    $montant = parse_user_amount($_POST['montant']);
    
    // Appel serveur
    $code = $MADMIN->transfert($montant, $_POST["userId"]);

    // Si le virement a échoué
    if($code != 1){
        $erreur = str_getcsv(substr($MADMIN->getErrorDetail($code), 0, -2));
        $app->flash('virement_erreur', '<p>Erreur n°'.$erreur[0].' : <b>'.$erreur[1].'</b></p><p>'.$erreur[2].'</p>');
        $app->flash('virement_value', $montant/100);
    } else {
        $app->flash('virement_ok', 'Le virement de '.format_number($montant).' € à réussi.');
    }
    
    // Retour vers la page d'accueil
    $app->response()->redirect($app->urlFor('home'));
});

// --- Enregistrement

// Affichage de la charte
$app->get('/register', function() use ($app, $CONF) {
    $app->render('header.php', array(
        "title" => $CONF["title"]
    ));

    $app->render('register.php');

    $app->render('footer.php');
})->name('register');

// Enregistrement après validation de la charte
$app->post('/register', function() use ($app, $MADMIN) {
    // Appel serveur
    $result = $MADMIN->register();
    
    if(isset($result["success"])) {
        // Si ok, go vers la page d'accueil
        $app->redirect($app->urlFor('home'));
    } else {
        if(isset($result["error_msg"])) {
            // Si on a une erreur on l'affiche
            $app->flash('register_erreur', $result["error_msg"]);
        } else {
            $app->flash('register_erreur', "Échec de la création du compte.");
        }
        
        // On n'a pas réussi à s'enregistrer, retour vers la charte
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