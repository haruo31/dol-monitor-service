<?php

use ActiveRecord\Model;
class ServiceTest extends Model {
	static $has_many = array('service_test_statuses', 'config_services');
}
