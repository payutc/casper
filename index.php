<?php
require "config.php";
require "madmin.php";
require "auth.php";
require "reload.php";
require "virement.php";

$userName = $MADMIN->getFirstname()." ".$MADMIN->getLastname();

if(isset($_GET["block"]))
{
  $MADMIN->blockMe();
}

if(isset($_GET["unblock"]))
{
  $MADMIN->deblock();
}

?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $CONF["title"]?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- pagination de l'historique -->
    <script type="text/javascript">
        var historique;
        var rows;
        var rowCount = 0;
        var pageSize = 15;
        var pageIndex = 0;
        var pages = 0;
        
        function init(){
          historique = document.getElementById("historique");
          rows = historique.getElementsByTagName("tr");
          rowCount = rows.length;
          pages = Math.ceil(rowCount / pageSize);
          
          if(pages<=10)
          {
            for ( var i=1; i <= pages; i++){
                    var paging = document.getElementById("paging");
                    paging.innerHTML += "<li><a onclick='selectPage(" + i + ");'>" + i + "</a></li>";
            }
          } /* else {
            // Il faut mettre des boutons suivants précédents...
            // La fonction est placé dans selectPage car on regénére le menu à chaque selection de pages...
          }*/
        }
        
        function selectPage(pageIndex){
          var current = (pageSize * (pageIndex - 1));
          var next = (current + pageSize < rowCount) ? current + pageSize : rowCount;
          var paging = document.getElementById("paging");

          if(pages<=10)
          {
            var button = paging.getElementsByTagName("li");
            for(var i=0; i<pages; i++)
              button[i].className="";
            button[pageIndex-1].className="active";
          } else {
            posCurrent = 3;
            if(pageIndex<3)
              posCurrent = pageIndex;
            start = (pageIndex - 3 < 0) ? 0 : pageIndex - 3;
            if(start + 5 < pages) {
              end = start + 6;
            } else {
              start = pages - 5;
              end = pages + 1;
              posCurrent = 5 - (pages - pageIndex); 
            }
            if(start == 0)
              paging.innerHTML = "<li><a>" + "<<" + "</a></li>";
            else
              paging.innerHTML = "<li><a onclick='selectPage(" + start + ");'>" + "<<" + "</a></li>";

            for ( var i=start+1; i < end; i++){
              var paging = document.getElementById("paging");
              paging.innerHTML += "<li><a onclick='selectPage(" + i + ");'>" + i + "</a></li>";
            }

            if(end>pages)
              paging.innerHTML += "<li><a>" + ">>" + "</a></li>";
            else
              paging.innerHTML += "<li><a onclick='selectPage(" + end + ");'>" + ">>" + "</a></li>";

            var button = paging.getElementsByTagName("li");
            for(var i=0; i<6; i++)
              button[i].className="";
            button[posCurrent].className="active";
            if(start==0)
              button[0].className="disabled";
            if(end>pages)
              button[6].className="disabled";
          }

          for (var idx =0; idx < current; idx++){
                  rows[idx].style.display ='none';
          }
          
          for (var idx = current; idx < next; idx++){
                  rows[idx].style.display = 'table-row';
          }
          
          
          for (var idx = next; idx < rowCount; idx++){
                  rows[idx].style.display ='none';
          }
        }
    </script>

  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#"><?php echo $CONF["title"]?></a>
          <div class="nav-collapse">
            <p class="navbar-text pull-right"><a href="?logout">déconnexion</a></p>
          </div>
        </div>
      </div>
    </div>

    <div class="container">

      <div class="hero-unit">
        <h1>Bonjour, <?php echo $userName?> !</h1>
	<br />
        <p>Ton solde payutc est de : <strong><?php echo format_amount($MADMIN->getCredit()); ?> €</strong></p>
      </div>
      <div class="row">
        <div class="span7" >
          <h2>Historique</h2>
           <div><?php echo affichage_histo($MADMIN); ?></div>
        </div>
        <div class="span5">
          <h2>Rechargement</h2>
          <?php
            $max_reload = $MADMIN->getMaxReload();
            $min_reload = $MADMIN->getMinReload();
            if($max_reload != 0) { 
  						if(isset($error_reload)) { ?>
            <div class="alert alert-error">
  						<?php echo $error_reload?>
  					</div>
  					<?php } ?>
  					<?php
  						if(isset($success_reload)) { ?>
            <div class="alert alert-success">
  						<?php echo $success_reload?>
  					</div>
  					<?php } ?>
  					<form action="<?php echo $_SERVER['PHP_SELF']?>?reload" method="post" class="well form-inline">
             <p><h6>Montant du rechargement : </h6><br />
             <div class="input-prepend input-append">
  						 	<span class="add-on">€</span>
  							<input name="montant" type="number" class="span1" min="<?php echo $min_reload/100?>" max="<?php echo $max_reload/100?>" value="<?php echo $reload_value?>" step="0.01" />
  							<button type="submit" class="btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Recharger</button></p>
  					 </div>
            </form>
            <?php } else { ?>
              <div class="alert alert-success">
                Ton compte ne peut être rechargé sans dépasser le plafond maximum.
              </div>
            <?php } ?> 
            <br />
            <h2>Etat du compte <?php echo affichage_blocage($MADMIN)?></h2>
            <div class="well">
              En cas de perte ou vol de ton badge.<br />
              Tu peux ici, bloquer/débloquer la possibilité de payer avec ta carte.<br />
              <?php echo button_blocage($MADMIN)?><br />
            </div>
            <h2>Virement à un ami</h2>
            <?php echo $virement($MADMIN); ?>
       </div>
      </div>

      <hr>

      <footer>
        <p>&copy; payutc 2012</p>
      </footer>

    </div> <!-- /container -->
    <script src="js/jquery-1.7.2.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script type="text/javascript">
            init();
            selectPage(1);
    </script>
    <?php echo virement_js()?>
    </script> 
  </body>
</html>
