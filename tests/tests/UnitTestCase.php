<?php

use Phalcon\Di;
use Phalcon\Test\UnitTestCase as PhalconTestCase;
use Shop_categories\Tests\Mocks\RequestMock;
use Shop_categories\Tests\Mocks\ResponseMock;

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