<?php

interface DOLServiceTest {
    function testConnection();
    function testTranslation();

    /**
     * the result of this method would be recorded in monitor service db.
     * make array return value when finish the method.
     * array('time' => <response time>, 'degraded' => <boolean value, true when service response too slow>)
     */
    function testResponseTime();
}
