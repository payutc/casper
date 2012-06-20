<?php

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
				$MADMIN->getFirstname();
		} catch (Exception $e) {
				session_destroy();
				// On envoie sur le cas
				header("Location: ".$MADMIN->getCasUrl()."/login?service=".$CONF['casper_url']);
				exit();
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
			$code = $MADMIN->loginCas($ticket, $CONF['casper_url']);
		} catch (Exception $e) {
				echo "<pre>".$e."</pre>";
		}
		if($code == 1)
		{
			$_SESSION['cookies'] = $MADMIN->_cookies;
			$_SESSION['loged'] = 1;
			// Pas obligatoire mais c'est mieux pour virer le ticket de la barre d'adresse
			header("Location: ".$CONF['casper_url']);
		  	exit();
		} else {
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
