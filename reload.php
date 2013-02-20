<?php
$reload_value = 10.00;
if(isset($_GET["reload"]))
{
	if(isset($_POST["montant"]))
	{
		$amount = $_POST["montant"] * 100;
		$reload_value = $amount/100;
		$can = $MADMIN->canReload($amount);
		if($can == 1)
		{
			// On peut recharger
			echo $MADMIN->reload($amount, $CONF['casper_url']);
			exit();
		} else {
			$details_erreur = str_getcsv(substr($MADMIN->getErrorDetail($can), 0, -2));
			$error_reload = "<p>Erreur n°".$details_erreur[0]." : <b>".$details_erreur[1]."</b></p>";
			$error_reload .= "<p>".$details_erreur[2]."</p>";
		}
	}
}


if(isset($_GET["paybox"]))
{
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
		$error_reload = "<p>Erreur PAYBOX n° $num_err</p>";
	} else if($_GET['paybox'] == 'annule') { // On a une annulation
		$error_reload = "<p>Vous avez annulé le rechargement.</p>";	
	} else if($_GET['paybox'] == 'refuse') { // la transaction a ete refuse
		$error_reload = "<p>Transaction refusée</p>";
	} else if($_GET['paybox'] == 'effectue') { // a priori ça a l'air bon
		$success_reload = "<p>Votre compte à été rechargé.</p>";
	}

}
