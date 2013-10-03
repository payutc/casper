<?php
// Slim
require 'vendor/autoload.php';
use \Payutc\Casper\Config;
use \Payutc\Casper\JsonClientFactory;
use \Payutc\Casper\JsonClientMiddleware;

// Load configuration
require "config.inc.php";
Config::initFromArray($_CONFIG);

// Settings for cookies
$sessionPath = parse_url(Config::get("casper_url"), PHP_URL_PATH);
session_set_cookie_params(0, $sessionPath);
session_start();

// Slim initialization
$app = new \Slim\Slim(Config::get('slim_config'));

// This middleware loads all our json clients
$app->add(new JsonClientMiddleware);

// Route middleware to check that a user is logged in
function userLoggedIn(){
    $app = \Slim\Slim::getInstance();
    
    // On récupère les infos du user
    $status = JsonClientFactory::getInstance()->getClient("MYACCOUNT")->getStatus();
    
    if(empty($status->application)){
        $app->getLog()->debug("No app logged in, calling loginApp");
        // Connexion de l'application
        try {
            JsonClientFactory::getInstance()->getClient("MYACCOUNT")->loginApp(array(
                "key" => Config::get("application_key")
            ));
        } catch (\JsonClient\JsonException $e) {
            $app->getLog()->error("Application login error: ".$e->getMessage());
            throw $e;
        }
    }
    
    // Si on a aucun user chargé, on repasse par le cas
    if(empty($status->user)){
        $app->getLog()->debug("No user logged in, redirect to login route");
        $app->redirect($app->urlFor('login'));
	}
    
    $env = $app->environment();
    $env["user_data"] = $status->user_data;
}

// A few helpers to handle amounts in cents
function format_amount($val) {
	return number_format($val/100, 2, ',', ' ');
}

function parse_user_amount($val) {
    $amount = str_replace(',','.', $val);
    return $amount*100;
}

// --- Coeur de casper

// Page principale
$app->get('/', 'userLoggedIn', function() use($app) {
    $app->render('header.php', array(
        "title" => Config::get("title")
    ));
    
    $reloadInfo = JsonClientFactory::getInstance()->getClient("RELOAD")->info();
    $account = JsonClientFactory::getInstance()->getClient("MYACCOUNT")->historique();
    $blockedStatus = JsonClientFactory::getInstance()->getClient("MYACCOUNT")->isBlockedMe();
    
    $env = $app->environment();
    
    $app->render('main.php', array(
        "userDetails" => array(
            "firstname" => $env["user_data"]->firstname,
            "lastname" => $env["user_data"]->lastname,
            "credit" => $account->credit
        ),
        "max_reload" => $reloadInfo->max_reload,
        "min_reload" => $reloadInfo->min,
        "historique" => $account->historique,
        "isBlocked" => $blockedStatus
    ));
    $app->render('footer.php');
})->name('home');

// Blocage du compte
$app->get('/block', 'userLoggedIn', function() use ($app) {
    JsonClientFactory::getInstance()->getClient("MYACCOUNT")->setSelfBlock(array(
        "blocage" => true
    ));
    $app->response()->redirect($app->urlFor('home'));
});

// Déblocage du compte
$app->get('/unblock', 'userLoggedIn', function() use ($app) {
    JsonClientFactory::getInstance()->getClient("MYACCOUNT")->setSelfBlock(array(
        "blocage" => false
    ));
    $app->response()->redirect($app->urlFor('home'));
});

// Autocomplete du virement
$app->get('/ajax', 'userLoggedIn', function() use ($app) {
    if(!empty($_GET["q"])) {
        $search = JsonClientFactory::getInstance()->getClient("RELOAD")->userAutocomplete(array(
            "queryString" => $_GET["q"]
        ));
        
        echo json_encode($search);        
    }
});

// Départ vers le rechargement
$app->post('/reload', 'userLoggedIn', function() use ($app) {
    if(empty($_POST["montant"])) {
        $app->flash('error_reload', "Saisissez un montant");
        $app->response()->redirect($app->urlFor('home'));
    }

    $amount = parse_user_amount($_POST['montant']);
    
    try {
        $reloadUrl = JsonClientFactory::getInstance()->getClient("RELOAD")->reload(array(
            "amount" => $amount,
            "callbackUrl" => Config::get("casper_url")
        ));
        $app->redirect($reloadUrl);
    }
    catch(\JsonClient\JsonException $e){
        $app->flash('reload_erreur', '<p>Erreur</p><p>'.$e->getMessage().'</p>');
        $app->flash('reload_value', $amount/100);
        $app->getLog()->error("Reload failed: ".$e->getMessage());
        $app->response()->redirect($app->urlFor('home'));
    }
});

// Virement à un ami
$app->post('/virement', 'userLoggedIn', function() use ($app) {
    // Récupèration du montant en cents
    $montant = parse_user_amount($_POST['montant']);
    
    try {
        $virement = JsonClientFactory::getInstance()->getClient("TRANSFER")->transfer(array(
            "amount" => $montant,
            "userID" => $_POST['userId'],
            "message" => $_POST['message']
        ));
        
        $app->flash('virement_ok', 'Le virement de '.format_amount($montant).' € à réussi.');
    }
    catch(\JsonClient\JsonException $e){
        $app->flash('virement_erreur', $e->getMessage());
    }
    
    // Retour vers la page d'accueil
    $app->response()->redirect($app->urlFor('home'));
});

// --- Enregistrement

// Affichage de la charte
$app->get('/register', function() use ($app) {
    $app->render('header.php', array(
        "title" => Config::get("title")
    ));

    $app->render('register.php');

    $app->render('footer.php');
})->name('register');

// Enregistrement après validation de la charte
$app->post('/register', function() use ($app) {
    try {
        // Appel serveur
        $result = JsonClientFactory::getInstance()->getClient("MYACCOUNT")->register();
        
        // Si ok, go vers la page d'accueil
        $app->redirect($app->urlFor('home'));
    }
    catch(\JsonClient\JsonException $e){
        // Si on a une erreur on l'affiche
        $app->flash('register_erreur', $e->getMessage());
        
        // On n'a pas réussi à s'enregistrer, retour vers la charte
        $app->redirect($app->urlFor('register'));
    }
});

// --- CAS
$app->get('/login', function() use ($app) {
    // Si pas de ticket, c'est une invitation à se connecter
    if(empty($_GET["ticket"])) {
        $app->getLog()->debug("No CAS ticket, unsetting cookies and redirecting to CAS");
        // On jette les cookies actuels
        JsonClientFactory::getInstance()->destroyCookie();
        
        // Redirection vers le CAS
        $app->redirect(JsonClientFactory::getInstance()->getClient("MYACCOUNT")->getCasUrl()."/login?service=".Config::get("casper_url").'login');
    } else {
        // Connexion au serveur avec le ticket CAS
        try {
            $app->getLog()->debug("Trying loginCas");
            
            $result = JsonClientFactory::getInstance()->getClient("MYACCOUNT")->loginCas(array(
                "ticket" => $_GET["ticket"],
                "service" => Config::get("casper_url").'login'
            ));
        } catch (\JsonClient\JsonException $e) {
            // Si l'utilisateur n'existe pas, go inscription
            if($e->getType() == "Payutc\Exception\UserNotFound"){
                // On doit garder le cookie car le serveur garde le login de son côté
                JsonClientFactory::getInstance()->setCookie(JsonClientFactory::getInstance()->getClient("MYACCOUNT")->cookie);
                
                // Redirection vers la charte
                $app->redirect($app->urlFor('register'));
            }
            
            $app->getLog()->warn("Error with CAS ticket ".$_GET["ticket"].": ".$e->getMessage());
            
            // Affichage d'une page avec juste l'erreur
            $app->render('header.php', array("title" => Config::get("title", "payutc")));
            $app->render('error.php', array('login_erreur' => 'Erreur de login CAS<br /><a href="'.$app->urlFor('login').'">Réessayer</a>'));
            $app->render('footer.php');
            $app->stop();
        }

        // On stocke le cookie
        JsonClientFactory::getInstance()->setCookie(JsonClientFactory::getInstance()->getClient("MYACCOUNT")->cookie);
            
        // Go vers la page d'accueil
        $app->redirect($app->urlFor('home'));
    }
})->name('login');


$app->run();