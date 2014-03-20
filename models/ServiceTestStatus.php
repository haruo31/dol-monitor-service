<?php

use ActiveRecord\Model;

class ServiceTestStatus extends Model {
	static $belongs_to = array(
	        array('service_test', 'class_name' => 'ServiceTest')
	);

	const STATUS_DOWN = 'DOWN';
	const STATUS_OK = 'OK';
	const STATUS_DEGRADED = 'DEGRADED';
	const STATUS_UNKNOWN = 'UNKNOWN';
}
