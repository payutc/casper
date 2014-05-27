<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- Le styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <link rel="shortcut icon" href="img/favicon.ico" />

    <script src="js/jquery-1.9.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/casper.js"></script>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>

  <body>
    <div class="navbar navbar-fixed-top navbar-inverse">
      <div>
        <div class="container">
          <a class="navbar-brand" href=""><img src="img/payutc_rect_110.png" alt="<?php echo $title ?>"></a>
          <div>
            <?php if($loggedin): ?>
            <p class="navbar-text pull-right"><a href="logout">déconnexion</a></p>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
