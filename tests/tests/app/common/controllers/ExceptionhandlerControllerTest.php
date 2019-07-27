<?php
/**
 * User: Wajdi Jurry
 * Date: 26/10/18
 * Time: 06:23 م
 */

namespace tests\app\common\controllers;

use Phalcon\Logger\Adapter\File;
use PHPUnit\Framework\MockObject\MockObject;
use app\common\controllers\ExceptionhandlerController;
use app\common\logger\ApplicationLogger;
use tests\mocks\ResponseMock;

class ExceptionhandlerControllerTest extends \UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function getControllerMock(...$methods)
    {
        return $this->getMockBuilder(ExceptionhandlerController::class)
            ->setMethods($methods)
            ->getMock();
    }

    public function getFileObjectMock(...$methods)
    {
        return $this->getMockBuilder(File::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function responseSamples()
    {
        return [
            [
                [
                    'status' => 400,
                    'message' => ['error1', 'error2']
                ]
            ],
            [
                [
                    'status' => 500,
                    'message' => 'sample error'
                ]
            ],
            [
                [
                    'status' => 404,
                    'message' => 'not found error'
                ]
            ]
        ];
    }

    /**
     * @param $expectedResponse
     *
     * @dataProvider responseSamples
     */
    public function testRaiseErrorAction($expectedResponse)
    {
        /** @var ResponseMock $response */
        $response = $this->di->get('response');

        /** @var ExceptionhandlerController|MockObject $controllerMock */
        $controllerMock = $this->getControllerMock('getLogger');

        /** @var ApplicationLogger|MockObject */
        $applicationLoggerMock = $this->getMockBuilder(ApplicationLogger::class)
            ->setMethods(['logError'])
            ->getMock();
        $applicationLoggerMock->expects(self::once())->method('logError')->with($expectedResponse['message']);
        $controllerMock->expects(self::once())->method('getLogger')->willReturn($applicationLoggerMock);

        $controllerMock->raiseErrorAction($expectedResponse['message'], $expectedResponse['status']);

        $this->assertEquals($response->jsonContent, $expectedResponse);
    }

    public function errorsSamples()
    {
        return [
            [
                implode( '\n', ['error1', 'error2'])
            ],
            [
                'error1'
            ]
        ];
    }
}
