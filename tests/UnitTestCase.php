<?php

/**
 * User: Wajdi Jurry
 * Date: 20/03/2020
 * Time: 06:27 PM
 */

use mocks\ApplicationLogger;
use Phalcon\Di;
use Phalcon\Test\UnitTestCase as PhalconTestCase;
use app\tests\mocks\RequestMock;
use app\tests\mocks\ResponseMock;

abstract class UnitTestCase extends PhalconTestCase
{
    public function setUp()
    {
        parent::setUp();

        // Load any additional services that might be required during testing
        $di = Di::getDefault();

        // Get any DI components here. If you have a config, be sure to pass it to the parent
        $di->setShared('request', RequestMock::class);
        $di->setShared('response', ResponseMock::class);
        $di->setShared('logger', ApplicationLogger::class);

        $this->setDi($di);
    }
}