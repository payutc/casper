<?php

// Permet à plusieurs instances de casper de tourner sur le même hôte
// (et aussi de ne pas se faire piquer des cookies)
$sessionPath = parse_url($CONF['casper_url'], PHP_URL_PATH);
session_set_cookie_params(0, $sessionPath);

session_start();

// L'utilisateur est déjà connecté avec un cookie
if(isset($_SESSION['cookies'])) {
    // on récupére la session soap
	$MADMIN->_cookies = $_SESSION['cookies'];

    $userDetails = $MADMIN->getUserDetails();
        
    // Si user vide de l'autre côté, on vide tout et on renvoie sur le cas
    if(empty($userDetails)) {
        session_destroy();
        header("Location: ".$MADMIN->getCasUrl()."/login?service=".$CONF['casper_url']);
        exit();
	}

	if(!$_SESSION['registered']) {
		if(isset($_GET["register"])) {
			$result = $MADMIN->register();
			if(!isset($result["success"])) {
				if(isset($result["error_msg"])) {
				    echo $result["error_msg"];
				}
				exit();
			} else {
				$_SESSION['registered'] = True;
				// Pas obligatoire mais c'est mieux pour virer le ?register de la barre d'adresse
				header("Location: ".$CONF['casper_url']);
	  			exit();
			}
		} else {
			include 'register.php';
			exit();
		}
	}
} else {
    // Utilisateur non connecté
	// 1. Regardons si on a un retour de CAS.
	if(isset($_GET["ticket"])) {
		// Connexion soap
		$result = $MADMIN->loginCas($_GET["ticket"], $CONF['casper_url']);

		if(isset($result["success"])) {
			$_SESSION['registered'] = true;
			$_SESSION['cookies'] = $MADMIN->_cookies;
			// Pas obligatoire mais c'est mieux pour virer le ticket de la barre d'adresse
			header("Location: ".$CONF['casper_url']);
		  	exit();
		} else if(isset($result["error"])) {
			$code = $result["error"];
            
    		// SI NON INSCRIT => PROCEDURE DE CREATION DE COMPTE
    		if($code == 405) {
    			$_SESSION['registered'] = False;

    			$_SESSION['cookies'] = $MADMIN->_cookies;
    			$_SESSION['loged'] = 1;
    			// Pas obligatoire mais c'est mieux pour virer le ticket de la barre d'adresse
    			header("Location: ".$CONF['casper_url']);
    		  	exit();
    		} else {
    			if(isset($result["error_msg"])) {
    			    echo $result["error_msg"];
    			} else {
    			    echo $MADMIN->getErrorDetail($code);
    			}
    			exit();
    		}
		}	
	} else {
		// 2. On renvoie sur le cas
		session_destroy();
		header("Location: ".$MADMIN->getCasUrl()."/login?service=".$CONF['casper_url']);
		exit();
	}
}
