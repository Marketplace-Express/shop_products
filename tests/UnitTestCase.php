<?php
/**
 * User: Wajdi Jurry
 * Date: 19/04/2019
 * Time: 02:59 PM
 */

use Phalcon\Di;
use Phalcon\Test\UnitTestCase as PhalconTestCase;
use tests\mocks\RequestMock;
use tests\mocks\ResponseMock;
use tests\mocks\ApplicationLogger;

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

        var_dump($di->getConfig()->database);

        $this->setDi($di);
    }
}