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
                        'status' => ServiceTestStatus::STATUS_EXPIRED,
                        'response_time' => 0
                ));

            }
        }
    }

    public static function isOk($cnf = null) {
        if ($cnf == null) {
            return FALSE;
        }

        $stats = self::getCurrentStatus($cnf);

        foreach ($stats as $stat) {
            if ($stat->status != ServiceTestStatus::STATUS_OK) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public static function getCurrentStatus($cnf = null) {
        if ($cnf == null) {
            return self::getCurrentStatusAll();
        }

        return self::getCurrentStatusByConfig(
            ConfigService::find('all', array('conditions' => array('name=?', $cnf))));
    }

    protected static function getCurrentStatusByConfig(array $cnf) {
        if ($cnf == null || count($cnf) < 1) {
            return array();
        }

        $id = array();
        foreach ($cnf as $c) {
            $id[] = $c->service_test_id;
        }
        $ids = implode(',', $id);

        $status = ServiceTestStatus::STATUS_EXPIRED;
        $dtime = new ActiveRecord\DateTime(
            date('c', time() - self::$testExpires));
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
    s.SERVICE_TEST_ID
   ,max(s.RUN_DATE) as LAST_RUN_DATE
  from
    SERVICE_TEST_STATUSES s
  where
    s.SERVICE_TEST_ID in ({$ids})
  group by
    s.SERVICE_TEST_ID) l
on
  t.SERVICE_TEST_ID = l.SERVICE_TEST_ID
  and t.RUN_DATE = l.LAST_RUN_DATE
SQL
            ;

        return ServiceTestStatus::find_by_sql($sql);
    }

    protected static function getCurrentStatusAll() {
        $status = ServiceTestStatus::STATUS_EXPIRED;
        $dtime = new ActiveRecord\DateTime(
            date('c', time() - self::$testExpires));
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

        return ServiceTestStatus::find_by_sql($sql);
    }

    protected static function getExpiredServices() {
        $dtime = new ActiveRecord\DateTime(
            date('c', time() - self::$testExpires));
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
            $tests = self::getExpiredServices();
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
