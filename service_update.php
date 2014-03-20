<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset="utf-8">
    <title>Update services</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css"
    rel="stylesheet">
    <link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css"
    rel="stylesheet">
    </head>

    <body>
    <div class="container">
    <div class="well">
<?php

      require_once dirname(__FILE__) . '/helper/autoload.php';
require_once dirname(__FILE__) . '/config/config.php';

ServiceTestController::updateAll();

?>
    <h3>
    Update Succeeded.
    </h3>
    This page will be updated automatically.
    <a href="./service_monitor.php" class="link link-default">Click here.</a>
    </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"
    >
    </script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"
    >
    </script>
    </body>
    </html>