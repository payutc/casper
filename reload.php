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
			header("Location: " . $MADMIN->reload($amount, $CONF['casper_url']));
			exit();
		} else {
			$details_erreur = str_getcsv(substr($MADMIN->getErrorDetail($can), 0, -2));
			$error_reload = "<p>Erreur n°".$details_erreur[0]." : <b>".$details_erreur[1]."</b></p>";
			$error_reload .= "<p>".$details_erreur[2]."</p>";
		}
	}
}


if(isset($_GET["token"]))
{

	header("Location: ".$CONF['casper_url']);
	exit();

}
