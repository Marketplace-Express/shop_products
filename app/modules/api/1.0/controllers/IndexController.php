<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:31 م
 */

namespace Shop_products\Modules\Api\Controllers;

use Shop_products\Controllers\BaseController;
use Shop_products\RequestHandler\Product\{
    AbstractCreateRequestHandler,
    DeleteRequestHandler,
    GetRequestHandler,
    UpdatePhysicalProductRequestHandler
};
use Shop_products\RequestHandler\ProductRequestResolver;
use Shop_products\Services\ProductsService;

/**
 * Class IndexController
 * @package Shop_products\Modules\Api\Controllers
 * @RoutePrefix('/api/1.0/products')
 */
class IndexController extends BaseController
{
    /** @var ProductsService $service */
    private $service;

    /** @var ProductRequestResolver */
    private $resolver;

    public function initialize()
    {
        $this->service = new ProductsService();
    }

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
        } catch (\Throwable $exception) {
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
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Post('/')
     */
    public function createAction()
    {
        try {
            $requestBody = $this->request->getJsonRawBody();
            /** @var ProductRequestResolver $resolver */
            $resolver= $this->getJsonMapper()->map($requestBody, new ProductRequestResolver());
            /** @var AbstractCreateRequestHandler $request */
            $request = $this->getJsonMapper()->map($requestBody, $resolver->resolve());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->create($request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Put('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @param $id
     */
    public function updateAction($id)
    {
        try {
            /** @var UpdatePhysicalProductRequestHandler $request */
            $request = $this->getJsonMapper()->map($this->request->getJsonRawBody(), new UpdatePhysicalProductRequestHandler());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->getService()->update($id, $request->toArray()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Delete('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @param $id
     */
    public function deleteAction($id)
    {
        try {
            /** @var DeleteRequestHandler $request */
            $request = $this->getJsonMapper()->map(new \stdClass(), new DeleteRequestHandler());
            $this->getService()->delete($id);
            $request->successRequest('Deleted');
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Get('/test')
     */
    public function testAction()
    {
        $this->getService()->sendSync(['Hello World!']);
    }
}