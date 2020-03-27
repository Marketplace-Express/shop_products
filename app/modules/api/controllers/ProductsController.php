<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:31 م
 */

namespace app\modules\api\controllers;


use app\common\requestHandler\product\{
    AbstractCreateRequestHandler,
    GetRequestHandler,
    UpdateQuantityRequestHandler,
    UpdateRequestHandler
};
use app\common\requestHandler\ProductRequestResolver;
use Phalcon\Http\Response\StatusCode;

/**
 * Class IndexController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/products')
 */
class ProductsController extends BaseController
{
    /**
     * @Get('/')
     * @param GetRequestHandler $request
     */
    public function getAllAction(GetRequestHandler $request)
    {
        try {
            /** @var GetRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getQuery(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->di->getAppServices('productsService')->getAll($request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Get('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @param $id
     */
    public function getAction($id)
    {
        try {
            $this->response->setJsonContent([
                'status' => 200,
                'message' => $this->di->getAppServices('productsService')->getProduct($id)
            ]);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Get('/owner')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param GetRequestHandler $request
     */
    public function getAllForAdminsAction(GetRequestHandler $request)
    {
        try {
            /** @var GetRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getQuery(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->di->getAppServices('productsService')->getAll($request->toArray(), $request->getAccessLevel()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Get('/owner/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param $id
     * @param GetRequestHandler $request
     */
    public function getForAdminsAction($id, GetRequestHandler $request)
    {
        try {
            /** @var GetRequestHandler $request */
            $request->requireCategoryId = true;
            $request = $this->di->get('jsonMapper')->map($this->request->getQuery(), $request);
            if(!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->di->getAppServices('productsService')->getProduct($request->getVendorId(), $request->getCategoryId(), $id));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Post('/')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param ProductRequestResolver $request
     */
    public function createAction(ProductRequestResolver $request)
    {
        try {
            /** @var ProductRequestResolver $resolver */
            $resolver = $this->di->get('jsonMapper')->map($this->request->getJsonRawBody(), $request);
            /** @var AbstractCreateRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getJsonRawBody(), $resolver->resolve());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->di->getAppServices('productsService')->create($request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Put('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
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
            $request->successRequest($this->di->getAppServices('productsService')->update($id, $request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Delete('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param $id
     */
    public function deleteAction($id)
    {
        try {
            $this->di->getAppServices('productsService')->delete($id);
            http_response_code(StatusCode::NO_CONTENT);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Put('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}/quantity')
     * @AuthMiddleware('app\common\events\middleware\RequestMiddlewareEvent')
     * @param $id
     * @param UpdateQuantityRequestHandler $request
     */
    public function updateQuantityAction($id, UpdateQuantityRequestHandler $request)
    {
        try {
            /** @var UpdateQuantityRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->di->getAppServices('productsService')->updateQuantity($id, $request->toArray()));

        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
