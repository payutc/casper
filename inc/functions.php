<?php

function get_histo($user) {
	$depense = str_getcsv($user->getHistoriqueAchats(0, time()),';','"','\\');
	$recharge = str_getcsv($user->getHistoriqueRecharge(0, time()),';','"','\\');
	$virement_in = str_getcsv($user->getHistoriqueVirementIn(0, time()),';','"','\\');
	$virement_out = str_getcsv($user->getHistoriqueVirementOut(0, time()),';','"','\\');
	$histo = array_merge($depense, $recharge, $virement_out, $virement_in);
	arsort($histo);
	$return = array();
	foreach ($histo as $elt)
	{
		$line = str_getcsv($elt);
		if (count($line) == 7)
		{
			$line['type'] = "DEPENSE";
			$return[] = $line;
		} else if (count($line) == 6) {
			$line['type'] = "RECHARGEMENT";
			$return[] = $line;
		} else if (count($line) == 5){
			$line['type'] = "VIREMENT".$line[4];
			$return[] = $line;
		}
	}
	return $return;
}

function format_amount($val) {
	return number_format($val/100, 2, ',', ' ');
}

function parse_user_amount($val) {
    $amount = str_replace(',','.', $val);
    return $amount*100;
}

function auth(){
    global $MADMIN;
    
    $app = \Slim\Slim::getInstance();
    
    // L'utilisateur est déjà connecté avec un cookie
    if(isset($_SESSION['cookies'])) {
        // On charge la session soap
    	$MADMIN->_cookies = $_SESSION['cookies'];

        // On récupère les infos du user
        $userDetails = $MADMIN->getUserDetails();
        
        // Si le user est vide de l'autre côté, on repasse par le cas
        var_dump($userDetails);
        if(empty($userDetails)) {
            $app->redirect($app->urlFor('login'));
    	}
    } else {
    	$app->redirect($app->urlFor('login'));
    }
}