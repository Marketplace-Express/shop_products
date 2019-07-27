<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:31 م
 */

namespace app\modules\api\controllers;

use Exception;
use app\common\controllers\BaseController;
use app\common\requestHandler\product\{AbstractCreateRequestHandler,
    DeleteRequestHandler,
    GetRequestHandler,
    UpdateRequestHandler};
use app\common\requestHandler\ProductRequestResolver;
use app\common\services\ProductsService;
use app\common\utils\UuidUtil;
use stdClass;
use Throwable;

/**
 * Class IndexController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/1.0/products')
 */
class ProductsController extends BaseController
{
    /** @var ProductsService $service */
    private $service;

    public function initialize()
    {
        $this->service = new ProductsService();
    }

    /**
     * @return ProductsService
     */
    private function getService(): ProductsService
    {
        return $this->service;
    }

    /**
     * @Get('/')
     */
    public function getAllAction()
    {
        try {
            /** @var GetRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), new GetRequestHandler());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->getAll($request->toArray()));
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Get('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @param $id
     */
    public function getAction($id)
    {
        try {
            /** @var GetRequestHandler $request */
            $request = new GetRequestHandler();
            $request->requireCategoryId = true;
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->getProduct($request->getVendorId(), $request->getCategoryId(), $id));
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Get('/owner')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function getAllForAdminsAction()
    {
        try {
            /** @var GetRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), new GetRequestHandler());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->getAll($request->toArray(), $request->getAccessLevel()));
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Get('/owner/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param $id
     */
    public function getForAdminsAction($id)
    {
        try {
            /** @var GetRequestHandler $request */
            $request = new GetRequestHandler();
            $request->requireCategoryId = true;
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), $request);
            if(!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->getProduct($request->getVendorId(), $request->getCategoryId(), $id, $request->getAccessLevel()));
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Post('/')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function createAction()
    {
        try {
            $requestBody = $this->request->getJsonRawBody();
            /** @var ProductRequestResolver $resolver */
            $resolver = $this->getJsonMapper()->map($requestBody, new ProductRequestResolver());
            /** @var AbstractCreateRequestHandler $request */
            $request = $this->getJsonMapper()->map($requestBody, $resolver->resolve());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->create($request->toArray()));
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Put('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param $id
     */
    public function updateAction($id)
    {
        try {
            $vendorId = $this->request->getQuery('vendorId');
            if (!isset($vendorId) || !$this->getUuidUtil()->isValid($vendorId)) {
                throw new Exception('Invalid vendor id', 400);
            }
            /** @var UpdateRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->request->getJsonRawBody(), new UpdateRequestHandler());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->update($id, $request->toArray(), $vendorId));
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
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
            /** @var DeleteRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), new DeleteRequestHandler());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->getService()->delete($id, $request->getVendorId());
            $request->successRequest('Deleted');
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }
}