<?php

class ServiceTestController {
    private static $classes = NULL;

    public static $testExpires = 60;
    public static $initSql = NULL;

    public static function serviceTestPreset($class) {
        if (ServiceTestController::$classes === NULL) {
            ServiceTestController::$classes = array();
        }

        ServiceTestController::$classes[] = $class;
    }

    protected static function serviceNameFromPath($path) {
        $dir = dirname($path);
        $name = basename($path, 'Test.php');
        return $name . ':' . hash('crc32b', $dir);
    }

    protected static function init() {
        ConfigService::connection()->query(file_get_contents(ServiceTestController::$initSql));

        foreach (ServiceTestController::$classes as $cls) {
            $name = self::serviceNameFromPath($cls);

            $c = ServiceTest::find('first', array('conditions' => array('name = ?', $name)));
            if (!$c) {
                $c = ServiceTest::create(array(
                	'name' => $name,
                    'path' => $cls
                ));
                $cs = ServiceTestStatus::create(array(
                        'service_test_id' => $c->id,
                        'run_date' => '1970-01-01 00:00:00',
                        'status' => 'unknown',
                        'response_time' => 0
                ));

            }
        }
    }

    protected static function getExpired() {
        $expire = date('Y-m-d H:i:s', time() - ServiceTestController::$testExpires);

        return ServiceTest::find_by_sql(<<<SQL
select
  t.*
from
  `service_tests` t
inner join
 (select
    service_test_id
   ,max(run_date) as last_run_date
  from
    service_test_statuses
  group by
    service_test_id
  having
    last_run_date < '{$expire}') l
on
  t.id = l.service_test_id
SQL
);
    }

    public static function updateAll($force = false) {
        ServiceTestController::init();

        if ($force) {
            $tests = ServiceTestController::getExpired();
        } else {
            $tests = ServiceTest::find('all');
        }

        foreach ($tests as $test) {
            self::runTest($test);
        }
    }

    public static function runTest(ServiceTest $test) {
        include_once $test->path;
        $cls = explode(':', $test->name)[0] . 'Test';

        $suite = new $cls($cls);

        $result = $suite->run();

        print_r($result);
    }
}
