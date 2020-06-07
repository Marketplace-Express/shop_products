<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


use app\common\requestHandler\rate\CreateRequestHandler;
use app\common\requestHandler\rate\UpdateRequestHandler;
use app\common\services\RateService;

/**
 * Class RateController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/rate')
 */
class RateController extends BaseController
{
    /** @var RateService */
    private $service;

    /** @var \JsonMapper */
    private $mapper;

    /**
     * @param RateService $service
     */
    public function setService(RateService $service)
    {
        $this->service = $service;
    }

    /**
     * @param \JsonMapper $mapper
     */
    public function setMapper(\JsonMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function initialize()
    {
        $this->setService($this->di->getAppServices('rateService'));
        $this->setMapper($this->di->getJsonMapper());
    }

    /**
     * @Post('/')
     * @param CreateRequestHandler $request
     */
    public function createAction(CreateRequestHandler $request)
    {
        try {
            /** @var CreateRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest(call_user_func_array([$this->service, 'create'], $request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @param UpdateRequestHandler $request
     * @Put('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     */
    public function updateAction($id, UpdateRequestHandler $request)
    {
        try {
            /** @var UpdateRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            $request->rateId = $id;
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest(call_user_func_array([$this->service, 'update'], $request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Delete('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @param $id
     */
    public function deleteAction($id)
    {
        try {
            $this->service->delete($id);
            $this->response->setJsonContent([
                'status' => 200,
                'message' => 'Deleted'
            ]);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}