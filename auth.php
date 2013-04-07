<?php

// Permet à plusieurs instances de casper de tourner sur le même hôte
// (et aussi de ne pas se faire piquer des cookies)
$sessionPath = parse_url($CONF['casper_url'], PHP_URL_PATH);
session_set_cookie_params(0, $sessionPath);

session_start();

if(isset($_GET["logout"]))
{
	session_destroy();
	header("Location: ".$MADMIN->getCasUrl()."/logout?url=".$CONF['casper_url']);
  exit();
}

if(isset($_SESSION["loged"]) && $_SESSION["loged"] == 1) {
	// tout vas bien on est loged ;)
	// Si on a un cookie on récupére la session soap.
	if(isset($_SESSION['cookies'])) { 
		$MADMIN->_cookies = $_SESSION['cookies'];
		// Verification que la session soap n'a pas expiré. 
		try {
				if($MADMIN->getFirstname() == "") {
                    throw new Exception();
				}
		} catch (Exception $e) {
				session_destroy();
				// On envoie sur le cas
				header("Location: ".$MADMIN->getCasUrl()."/login?service=".$CONF['casper_url']);
				exit();
		}

		if(!$_SESSION['registered'])
		{
			if(isset($_GET["register"]))
			{
				$result = $MADMIN->register();
				if(!isset($result["success"]))
				{
					if(isset($result["error_msg"]))
						echo $result["error_msg"];
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
		// On délogue par sécurité
		session_destroy();
		// On envoie sur le cas
		header("Location: ".$MADMIN->getCasUrl()."/login?service=".$CONF['casper_url']);
		exit();
	}
} else {
	// User not loged
	//1. Regardons si on a un retour de CAS.
	if(isset($_GET["ticket"])) {
		// Connexion soap
		$ticket = $_GET["ticket"];
		try {
			$result = $MADMIN->loginCas($ticket, $CONF['casper_url']);
		} catch (Exception $e) {
				echo "<pre>".$e."</pre>";
		}

		if(isset($result["success"]))
			$code = 1;
		else if(isset($result["error"]))
			$code = $result["error"];

		// SI CONNEXION REUSSI
		if($code == 1)
		{
			$_SESSION['registered'] = True;
			$_SESSION['cookies'] = $MADMIN->_cookies;
			$_SESSION['loged'] = 1;
			// Pas obligatoire mais c'est mieux pour virer le ticket de la barre d'adresse
			header("Location: ".$CONF['casper_url']);
		  	exit();
		// SI NON INSCRIT => PROCEDURE DE CREATION DE COMPTE
		} else if($code == 405) {
			$_SESSION['registered'] = False;

			$_SESSION['cookies'] = $MADMIN->_cookies;
			$_SESSION['loged'] = 1;
			// Pas obligatoire mais c'est mieux pour virer le ticket de la barre d'adresse
			header("Location: ".$CONF['casper_url']);
		  	exit();
		} else {
			if(isset($result["error_msg"]))
				echo $result["error_msg"];
			else
				echo $MADMIN->getErrorDetail($code);
			exit();
		}
	} else {
		//2. On renvoie sur le cas
		session_destroy();
		header("Location: ".$MADMIN->getCasUrl()."/login?service=".$CONF['casper_url']);
		exit();
	}
}
