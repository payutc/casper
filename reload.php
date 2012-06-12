<?php
$reload_value = 10.00;
if(isset($_GET["reload"]))
{
	if(isset($_POST["montant"]))
	{
		$amount = $_POST["montant"] * 100;
		$reload_value = $amount/100;
		$can = $SADMIN->canReload($amount);
		if($can == 1)
		{
			// On peut recharger
			echo $SADMIN->reload($amount, $CONF['casper_url']);
			exit();
		} else {
			$error_reload = $SADMIN->getErrorDetail($can);
		}
	}
}


if(isset($_GET["paybox"]))
{
	if(isset($_GET["trans"])) {
		if($_GET['paybox'] == 'erreur') {
			header("Location: ".$CONF['casper_url']."?paybox=".$_GET["paybox"]."&NUMERR=".$_GET['NUMERR']);
		} else {
			header("Location: ".$CONF['casper_url']."?paybox=".$_GET["paybox"]);
		}
	}
	
	if($_GET['paybox'] == 'erreur') { 
		$num_err=$_GET['NUMERR'];
		$error_reload = "<p>Erreur PAYBOX n° $num_err</p>";
	} else if($_GET['paybox'] == 'annule') { // On a une annulation
		$error_reload = "<p>Vous avez annulé le rechargement.</p>";	
	} else if($_GET['paybox'] == 'refuse') { // la transaction a ete refuse
		$error_reload = "<p>Transaction refusée</p>";
	} else if($_GET['paybox'] == 'effectue') { // a priori ça a l'air bon
		$success_reload = "<p>Votre compte à été rechargé.</p>";
	}

}
