<?php

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
        $di->set('request', new RequestMock());
        $di->set('response', new ResponseMock());

        $this->setDi($di);
    }
}