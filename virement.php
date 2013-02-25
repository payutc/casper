<?php

if(isset($_GET["ajax"]))
{
  echo $MADMIN->getRpcUser($_GET["search"]);
  exit();
}

$virement_msg = "";
if(isset($_GET["virement"]))
{
	$montant = $_POST["montant"]*100;
	$code = $MADMIN->transfert($montant, $_POST["userId"]);
	sleep(1); // Gros HACK DEGEU, pour que le virement ai le temps de se faire et que l'historique puisse se charger avec le virement.
			  // Les fonctions soap semblent asynchrone... 
	header("Location: ".$CONF['casper_url']."?vir=$code&amount=$montant");
	exit();
}

if(isset($_GET["vir"]))
{
	$code = $_GET["vir"];
	$montant = $_GET["amount"]/100;
	if($code != 1) {
		$details_erreur = str_getcsv(substr($MADMIN->getErrorDetail($code), 0, -2));
		$erreur = "<p>Erreur n°".$details_erreur[0]." : <b>".$details_erreur[1]."</b></p>";
		$erreur .= "<p>".$details_erreur[2]."</p>";
		
		$virement_msg = '<div class="alert alert-error">
							'.$erreur.'
						</div>';
	} else {
		$virement_msg = '<div class="alert alert-success">
				Le virement de '.$montant.'€ à réussi.
			</div>';
	}
}


function virement($Class) {
	global $_SERVER, $CONF;
	global $virement_msg;
	$min_reload=0;
	$max_reload=$Class->getCredit()/100;

	return '
	<form action="'.$CONF['casper_url'].'?virement" method="post" class="well form-inline">
		'.$virement_msg.'
		<p><h6>Trouver un utilisateur : </h6><br />
			<input size="30" id="userName" name="userName" onkeyup="lookup(this.value);" type="text" autocomplete="off"/>
			<input id="userId" name="userId" type="text" style="display: none;" />
			<div class="suggestionsBox" id="suggestions" style="display: none;">
				<div class="suggestionList" id="autoSuggestionsList" style="list-style-type: none;"></div>
			</div>
		</p>
		<p><h6>Montant du virement : </h6><br />
		<div class="input-prepend input-append">
				 	<span class="add-on">€</span>
					<input name="montant" type="number" class="span1" min="'.$min_reload.'" max="'.$max_reload.'" value="0" step="0.01" />
					<button type="submit" class="btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Virer</button></p>
		</div>
	</form>';
}

function virement_js() {
	global $CONF;
return "
<script type=\"text/javascript\">
            function lookup(inputString) {
                if(inputString.length == 0) {
                    // Hide the suggestion box.
                    $('#suggestions').hide();
                } else {
                  $.get('".$CONF['casper_url']."?ajax', 'search='+inputString, function(data) {
                        if(data.length >0) {
                            if ( $(\"#userName\").is(\":focus\") ) 
                            	$('#suggestions').show();
                            $('#autoSuggestionsList').html(data);
                        }
                  });
                }
            } // lookup

            function fill(thisValue) {
              var elem = thisValue.split('!!!');
              id = elem[0];
              firstname = elem[1];
              lastname = elem[2];


              
                $('#userName').val(firstname + ' ' + lastname);
                $('#userId').val(id);
                $('#suggestions').hide();
            }

</script>";
}