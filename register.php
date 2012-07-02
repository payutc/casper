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
      <div class="row">
        <div class="span11 well">
          <h1>Première connexion à payutc</h1>
          <p>
            Cette charte qui n'est pas encore rédigé vise à indiquer à l'utilisateur :
            <ul>
              <li>La durée de conservation de ces données personnelles</li>
              <li>Dont le temps de conservation de son solde d'argent après son départ de l'utc.</li>
              <li>Ce que devient l'argent restant (genre va dans un fond de subvention pour les assos par exemple).</li>
              <li>etc.....</li>
            </ul>
          </p>
          Je déclare avoir lu et compris les termes de cette charte et je m’engage à les respecter.<br />    
          <a class="btn btn-primary btn-large pull-right" href="?register">J'accepte les conditions générales d'utilisation de payutc</a> 
        </div>
      </div>

      <hr>

      <footer>
        <p>&copy; payutc 2012</p>
      </footer>

    </div> <!-- /container -->
    <script src="js/jquery-1.7.2.min.js"></script>
    <script src="js/bootstrap.js"></script>
    </script> 
  </body>
</html>