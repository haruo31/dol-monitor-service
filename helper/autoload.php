<?php

spl_autoload_register ( function ($cls) {
    static $path = NULL;
    static $classes = NULL;
    if ($classes === NULL) {
        $path = dirname(__FILE__) . '/..';
        $classes = array (
                'servicetestcontroller' => '/controllers/ServiceTestController.php',
        );
    }
    $cn = strtolower($cls);
    if (isset($classes[$cn])) {
        require $path . $classes[$cn];
    }

} );

require_once dirname ( __FILE__ ) . '/../php-activerecord/ActiveRecord.php';
require_once dirname ( __FILE__ ) . '/../config/config.php';

