<?php
/**
 * User: Wajdi Jurry
 * Date: 26/10/18
 * Time: 11:25 م
 */

namespace Shop_categories\Tests\Controllers;

use PHPUnit\Framework\MockObject\MockObject;
use Shop_categories\Controllers\NotfoundController;
use Shop_categories\Tests\Mocks\ResponseMock;

class NotfoundControllerTest extends \UnitTestCase
{
    public function  setUp()
    {
        parent::setUp();
    }

    public function getControllerMock(...$methods)
    {
        return $this->getMockBuilder(NotfoundController::class)
            ->setMethods($methods)
            ->getMock();
    }

    public function testIndexAction()
    {
        /** @var NotfoundController|MockObject $controllerMock */
        $controllerMock = $this->getControllerMock('nothing');

        /** @var ResponseMock $response */
        $response = $this->di->get('response');

        $controllerMock->indexAction();

        $this->assertEquals(['status' => 404, 'message' => 'API not found'], $response->jsonContent);
    }
}
