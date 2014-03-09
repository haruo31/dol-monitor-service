<?php

use ActiveRecord\Model;

class ServiceTestStatus extends Model {
	static $belongs_to = array(
	        array('service_test', 'class_name' => 'ServiceTest')
	);
}
