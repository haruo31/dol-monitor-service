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
        $sqls = explode('/', preg_replace('/\s+/', ' ', file_get_contents(ServiceTestController::$initSql)));
        foreach ($sqls as $sql) {
            if ($sql != '' && $sql != ' ') {
                ConfigService::connection()->query($sql);
            }
        }

        foreach (ServiceTestController::$classes as $cls) {
            $name = self::serviceNameFromPath($cls);

            $c = ServiceTest::find('first', array('conditions' => array('name = ?', $name)));
            if (!$c) {
                $c = ServiceTest::create(array(
                    'name' => $name,
                    'path' => $cls
                ));

                ServiceTestStatus::create(array(
                        'service_test_id' => $c->id,
                        'run_date' => new ActiveRecord\DateTime('1970-01-01 00:00:00'),
                        'status' => ServiceTestStatus::STATUS_UNKNOWN,
                        'response_time' => 0
                ));

            }
        }
    }

    public static function getCurrentStatus() {
        $status = ServiceTestStatus::STATUS_UNKNOWN;
        $dtime = new ActiveRecord\DateTime(
            date('c', time() - ServiceTestController::$testExpires));
        $expire = $dtime->format('db');

        $sql = <<<SQL
select
  t.ID
 ,t.SERVICE_TEST_ID
 ,t.RUN_DATE
 ,case
  when RUN_DATE < '{$expire}'
  then '{$status}'
  else t.STATUS
  end as STATUS
 ,t.RESPONSE_TIME
from
  `SERVICE_TEST_STATUSES` t
inner join
 (select
    SERVICE_TEST_ID
   ,max(RUN_DATE) as LAST_RUN_DATE
  from
    SERVICE_TEST_STATUSES
  group by
    SERVICE_TEST_ID) l
on
  t.SERVICE_TEST_ID = l.SERVICE_TEST_ID
  and t.RUN_DATE = l.LAST_RUN_DATE
SQL
            ;
        var_dump($sql);

        return ServiceTestStatus::find_by_sql($sql);
    }

    protected static function getExpired() {
        $dtime = new ActiveRecord\DateTime(
            date('c', time() - ServiceTestController::$testExpires));
        $expire = $dtime->format('db');


        return ServiceTest::find_by_sql(<<<SQL
select
  t.*
from
  `SERVICE_TESTS` t
inner join
 (select
    SERVICE_TEST_ID
   ,max(RUN_DATE) as LAST_RUN_DATE
  from
    SERVICE_TEST_STATUSES
  group by
    SERVICE_TEST_ID
  having
    LAST_RUN_DATE < '{$expire}') l
on
  t.ID = l.SERVICE_TEST_ID
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

        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTestFile($test->path);

        $result = $suite->run();

        $pass = $result->passed();

        $restime = -1;

        foreach($pass as $k => $v) {
            if (strpos($k, 'testResponseTime') > 0) {
                var_dump($v);
                $restime = intval($v['result']['time']);
                $stat = $v['result']['degraded'] ? ServiceTestStatus::STATUS_DEGRADED : ServiceTestStatus::STATUS_OK;
            }
        }

        if ($result->failureCount() > 0) {
            $stat = ServiceTestStatus::STATUS_DOWN;
        }

        ServiceTestStatus::create(array(
            'service_test_id' => $test->id,
            'run_date' => new ActiveRecord\DateTime(),
            'status' => $stat,
            'response_time' => $restime
        ));
    }
}
