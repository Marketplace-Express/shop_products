<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:31 م
 */

namespace app\modules\api\controllers;

use app\common\exceptions\NotFound;
use app\common\requestHandler\product\{AbstractCreateRequestHandler,
    GetAllForAdminRequestHandler,
    GetAllRequestHandler,
    UpdateQuantityRequestHandler,
    UpdateRequestHandler};
use app\common\requestHandler\ProductRequestResolver;
use app\common\services\ProductsService;
use Phalcon\Http\Response\StatusCode;

/**
 * Class IndexController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/products')
 */
class ProductsController extends BaseController
{
    /** @var ProductsService */
    private $service;

    /** @var \JsonMapper */
    private $mapper;

    /**
     * @param ProductsService $service
     */
    protected function setService(ProductsService $service)
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
        $this->setService($this->di->getAppServices('productsService'));
        $this->setMapper($this->di->get('jsonMapper'));
    }

    /**
     * @Get('/')
     * @param GetAllRequestHandler $request
     */
    public function getAllAction(GetAllRequestHandler $request)
    {
        try {
            /** @var GetAllRequestHandler $request */
            $request = $this->mapper->map($this->request->getQuery(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->service->getAll($request->toArray()));
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
                'message' => $this->service->getProduct($id)
            ]);
        } catch (NotFound $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Get('/owner')
     * @param GetAllRequestHandler $request
     */
    public function getAllForAdminsAction(GetAllRequestHandler $request)
    {
        try {
            /** @var GetAllRequestHandler $request */
            $request = $this->mapper->map($this->request->getQuery(), $request);
            $request->vendorId = $this->di->getUserService()->vendorId;
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->service->getAll($request->toArray(), $request->getAccessLevel()));
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Get('/owner/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}')
     * @param $id
     */
    public function getForAdminsAction($id)
    {
        try {
            $this->response->setJsonContent([
                'status' => 200,
                'message' => $this->service->getProduct($id, true)
            ]);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Post('/')
     * @param ProductRequestResolver $request
     */
    public function createAction(ProductRequestResolver $request)
    {
        try {
            /** @var ProductRequestResolver $resolver */
            $resolver = $this->mapper->map($this->request->getJsonRawBody(), $request);
            /** @var AbstractCreateRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $resolver->resolve());
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->service->create($request->toArray()));
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
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->service->update($id, $request->toArray()));
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
            http_response_code(StatusCode::NO_CONTENT);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Put('/{id:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}/quantity')
     * @param $id
     * @param UpdateQuantityRequestHandler $request
     */
    public function updateQuantityAction($id, UpdateQuantityRequestHandler $request)
    {
        try {
            /** @var UpdateQuantityRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $request->successRequest($this->service->updateQuantity($id, $request->toArray()));

        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
