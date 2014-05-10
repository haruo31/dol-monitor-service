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

      require_once dirname(__FILE__) . '/../helper/autoload.php';
require_once dirname(__FILE__) . '/../config/config.php';

ServiceTestController::updateAll();

?>
    <h3>
    Update Succeeded.
    </h3>
    This page will be updated automatically.
    <a href="./service_monitor.php" class="link link-default">Click here</a> if you see this message.
    </div>
    </div>
    <script type='text/javascript'
    >
    window.location.href = './service_monitor.php';
    </script>
    </script>
    </body>
    </html>