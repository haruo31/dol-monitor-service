<?php

require_once dirname(__FILE__) . '/helper/autoload.php';
require_once dirname(__FILE__) . '/config/config.php';

foreach (ServiceTest::find('all', array('conditions' => array('name like ?', 'DOLLangrid%'))) as $t) {
    ConfigService::create(
        array(
            'name' => 'Langrid',
            'service_test_id' => $t->id
        )
    );
}
