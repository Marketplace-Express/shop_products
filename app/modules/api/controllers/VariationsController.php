<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


use app\common\requestHandler\variation\CreateRequestHandler;
use app\common\requestHandler\variation\UpdateRequestHandler;
use app\common\services\ProductsService;

/**
 * Class VariationsController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/variations')
 */
class VariationsController extends BaseController
{
    /**
     * @var ProductsService
     */
    private $service;

    public function initialize()
    {
        $this->service = $this->di->getAppServices('productsService');
    }

    /**
     * @Post('/')
     * @param CreateRequestHandler $request
     */
    public function createAction(CreateRequestHandler $request)
    {
        try {
            /** @var CreateRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->service->createVariation($request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Put('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @param $id
     * @param UpdateRequestHandler $request
     */
    public function updateAction($id, UpdateRequestHandler $request)
    {
        try {
            /** @var UpdateRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->service->updateVariation($id, $request->toArray()));
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
            $this->response->setJsonContent([
                'status' => 200,
                'message' => $this->service->deleteVariation($id)
            ]);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
