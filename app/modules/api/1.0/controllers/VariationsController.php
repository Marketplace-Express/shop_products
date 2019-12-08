<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


use app\common\controllers\BaseController;
use app\common\requestHandler\variation\CreateRequestHandler;
use app\common\services\ProductsService;

/**
 * Class VariationsController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/1.0/variations')
 */
class VariationsController extends BaseController
{
    /**
     * @return ProductsService
     */
    public function getService(): ProductsService
    {
    return new ProductsService();
    }

    /**
     * @Post('/{productId:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @AuthMiddleware('\app\common\events\middleware\RequestMiddlewareEvent')
     * @param $productId
     */
    public function createAction($productId)
    {
        try {
            /** @var CreateRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->request->getJsonRawBody(), new CreateRequestHandler($this));
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->createVariation($productId, $request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Delete('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @AuthMiddleware('\app\common\events\middleware\RequestMiddlewareEvent')
     * @param $id
     */
    public function deleteAction($id)
    {
        try {
            $this->response->setJsonContent([
                'status' => 200,
                'message' => $this->getService()->deleteVariation($id)
            ]);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
