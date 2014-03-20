<?php

/*
 * database setting
 */

ActiveRecord\Config::initialize(function ($cfg) {
    $cfg->set_model_directory(dirname(__FILE__) . '/../models');

	// see http://phpactiverecord.org/docs/ActiveRecord/Config
    $cfg->set_connections(array('development' => 'sqlite://unix(/tmp/dolddatastore.db)'));
});

ServiceTestController::$initSql = dirname(__FILE__) . '/tables.sql';

foreach (glob(dirname(__FILE__) . '/../service_tests/*Test.php') as $f) {
    ServiceTestController::serviceTestPreset($f);
}

