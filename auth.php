<?php

session_start();

if(isset($_GET["logout"]))
{
	session_destroy();
	header("Location: ".$CONF['cas_url']."/logout?url=".$CONF['casper_url']);
  exit();
}

if(isset($_SESSION["loged"]) && $_SESSION["loged"] == 1) {
	// tout vas bien on est loged ;)
	// Si on a un cookie on récupére la session soap.
	if(isset($_SESSION['cookies'])) { 
		$SADMIN->_cookies = $_SESSION['cookies'];
		// Verification que la session soap n'a pas expiré. 
		try {
				$SADMIN->getFirstname();
		} catch (Exception $e) {
				session_destroy();
				// On envoie sur le cas
				header("Location: ".$CONF['cas_url']."/login?service=".$CONF['casper_url']);
				exit();
		}
		
	} else {
		// On délogue par sécurité
		session_destroy();
		// On envoie sur le cas
		header("Location: ".$CONF['cas_url']."/login?service=".$CONF['casper_url']);
		exit();
	}
} else {
	// User not loged
	//1. Regardons si on a un retour de CAS.
	if(isset($_GET["ticket"])) {
		// Connexion soap
		$ticket = $_GET["ticket"];
		$code = $SADMIN->loginCas($ticket, $CONF['casper_url']);
		if($code == 1)
		{
			$_SESSION['cookies'] = $SADMIN->_cookies;
			$_SESSION['loged'] = 1;
			// Pas obligatoire mais c'est mieux pour virer le ticket de la barre d'adresse
			header("Location: ".$CONF['casper_url']);
		  	exit();
		} else {
			echo $SADMIN->getErrorDetail($code);
			exit();
		}
	} else {
		//2. On renvoie sur le cas
		session_destroy();
		header("Location: ".$CONF['cas_url']."/login?service=".$CONF['casper_url']);
		exit();
	}
}
