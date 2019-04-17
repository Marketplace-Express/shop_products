<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:31 م
 */

namespace Shop_products\Modules\Api\Controllers;

use Exception;
use Shop_products\Controllers\BaseController;
use Shop_products\RequestHandler\Product\{AbstractCreateRequestHandler,
    DeleteRequestHandler,
    GetRequestHandler,
    UpdateRequestHandler};
use Shop_products\RequestHandler\ProductRequestResolver;
use Shop_products\Services\ProductsService;
use Shop_products\Utils\UuidUtil;
use stdClass;
use Throwable;

/**
 * Class IndexController
 * @package Shop_products\Modules\Api\Controllers
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
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), new GetRequestHandler());
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
     * @AuthMiddleware("\Shop_products\Events\RequestMiddlewareEvent")
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
     * @AuthMiddleware("\Shop_products\Events\RequestMiddlewareEvent")
     * @param $id
     */
    public function getForAdminsAction($id)
    {
        try {
            /** @var GetRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->queryStringToObject($this->request->getQuery()), new GetRequestHandler());
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
     * @AuthMiddleware("\Shop_products\Events\RequestMiddlewareEvent")
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
     * @AuthMiddleware("\Shop_products\Events\RequestMiddlewareEvent")
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
     * @AuthMiddleware("\Shop_products\Events\RequestMiddlewareEvent")
     * @param $id
     */
    public function deleteAction($id)
    {
        try {
            /** @var DeleteRequestHandler $request */
            $request = $this->getJsonMapper()->map(new stdClass(), new DeleteRequestHandler());
            $this->getService()->delete($id);
            $request->successRequest('Deleted');
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }
}