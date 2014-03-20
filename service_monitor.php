<!DOCTYPE html>
<?php

require_once dirname(__FILE__) . '/helper/autoload.php';
require_once dirname(__FILE__) . '/config/config.php';

$statuses = ServiceTestController::getCurrentStatus();

?>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <title>
    </title>
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
        <h3>
          Language Service Monitor
        </h3>
        <a href="./service_update.php" class="btn btn-primary">Update Expired</a>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th> Test id </th>
            <th> Group </th>
            <th> Status </th>
            <th> Last update </th>
            <th> </th>
          </tr>
        </thead>
        <tbody>
<?php

    foreach ($statuses as $s) {
        if ($s->status == ServiceTestStatus::STATUS_OK) {
            $statLabel = '<span class="label label-success">OK</span>';
        } else if ($s->status == ServiceTestStatus::STATUS_DOWN) {
            $statLabel = '<span class="label label-danger">DOWN</span>';
        } else if ($s->status == ServiceTestStatus::STATUS_DEGRADED) {
            $statLabel = '<span class="label label-danger">DEGRADED</span>';
        } else {
            $statLabel = '<span class="label label-default">UNKNOWN</span>';
        }

        $confs = ConfigService::find('all', array('conditions' =>
        array('service_test_id=?', $s->service_test_id)));

        $confLabel = '';
        foreach ($confs as $c) {
            $confLabel .= '<span class="label label-primary">' . $c->name . '</span>';
        }

        echo <<<ML
          <tr>
            <td>
              {$s->service_test->name}
            </td>
            <td>
              {$confLabel}
            </td>
            <td>
              {$statLabel}
            </td>
            <td>
              {$s->run_date->format('iso8601')}
            </td>
            <td>
              <a href="#" class="btn btn-default btn-xs">Update</a>
            </td>
          </tr>
ML
        ;
    }

?>
        </tbody>
      </table>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"
    >
    </script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"
    >
    </script>
  </body>

</html>
