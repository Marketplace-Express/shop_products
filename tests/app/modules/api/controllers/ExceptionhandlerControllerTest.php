<?php
/**
 * User: Wajdi Jurry
 * Date: 26/10/18
 * Time: 06:23 م
 */

namespace app\tests\app\modules\api\controllers;

use app\modules\api\controllers\ExceptionhandlerController;
use app\tests\mocks\ResponseMock;

class ExceptionhandlerControllerTest extends \UnitTestCase
{
    /** @var ExceptionhandlerController */
    private $controller;

    public function setUp()
    {
        $this->controller = new ExceptionhandlerController();
        parent::setUp();
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
     * @dataProvider responseSamples
     */
    public function testRaiseErrorAction($expectedResponse)
    {
        /** @var ResponseMock $response */
        $response = $this->di->get('response');

        $this->controller->raiseErrorAction($expectedResponse['message'], $expectedResponse['status']);

        $this->assertEquals($response->jsonContent, $expectedResponse);
    }
}
