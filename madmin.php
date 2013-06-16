<?php

$MADMIN = @new SoapClient($CONF['soap_url']);

/*
echo "<pre>";
print_r($MADMIN->__getFunctions());
echo "</pre>";
*/

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

function affichage_histo($user) {
	$histo = get_histo($user);
	$return = "<table id='historique' class='table table-striped'>";
	foreach ($histo as $elt)
	{
		$return .= '<tr>';
		if ($elt['type'] == "DEPENSE") 
		{
			$return .= '<td>'.date('d/m/y H:i:s',$elt[0]).'</td><td>'.$elt[1].' ('.$elt[4].')</td><td><span class="label label-important"> <i class="icon-minus icon-white"></i> '.format_amount($elt[6]).' €</span></td>';
		} else if ($elt['type'] == "RECHARGEMENT") {
			$return .= '<td>'.date('d/m/y H:i:s',$elt[0]).'</td><td>Rechargement</td><td><span class="label label-success"> <i class="icon-plus icon-white"></i> '.format_amount($elt[5]).' €</span></td>';
		} else if ($elt['type'] == "VIREMENTin") {
			$return .= '<td>'.date('d/m/y H:i:s',$elt[0]).'</td><td>Virement ('.$elt[2].') '.$elt[3].'</td><td><span class="label label-success"> <i class="icon-plus icon-white"></i> '.format_amount($elt[1]).' €</span></td>';
		} else if ($elt['type'] == "VIREMENTout") {
			$return .= '<td>'.date('d/m/y H:i:s',$elt[0]).'</td><td>Virement ('.$elt[2].') '.$elt[3].'</td><td><span class="label label-important"> <i class="icon-minus icon-white"></i> '.format_amount($elt[1]).' €</span></td>';
		}
		$return .= "</tr>";
	}
	$return .= "</table>";
	$return .= '<div class="pagination pagination-centered"><ul id="paging"></ul></div>';
	return $return;
}

function affichage_blocage($Class) {
	$b = $Class->isBlocked();
	if($b == 1) {
		return "<span class=\"label label-important\">Bloqué <i class=\"icon-remove icon-white\"></i></span>";
	} else {
		return "<span class=\"label label-success\">Débloqué <i class=\"icon-ok icon-white\"></i></span>";
	}
}

function button_blocage($Class) {
	$b = $Class->isBlocked();
	if($b == 1) {
		return "<a class=\"btn btn-success pull-right\" href=\"?unblock\">Débloquer mon compte</a>";
	} else {
		return "<a class=\"btn btn-danger pull-right\" href=\"?block\">Bloquer mon compte</a>";
	}
}
