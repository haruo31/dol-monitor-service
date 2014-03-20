<?php

use ActiveRecord\Model;
class ConfigService extends Model {
    static $belongs_to = array('service_test');
}
