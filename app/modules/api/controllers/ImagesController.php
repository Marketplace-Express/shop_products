<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


use app\common\services\ImageService;
use app\common\requestHandler\image\{
    DeleteRequestHandler,
    MakeMainImageRequestHandler,
    UpdateOrderRequestHandler,
    UploadRequestHandler
};

/**
 * Class ImagesController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/images')
 */
class ImagesController extends BaseController
{
    /** @var ImageService */
    private $service;

    /** @var \JsonMapper */
    private $mapper;

    /**
     * @param ImageService $service
     */
    protected function setService(ImageService $service)
    {
        $this->service = $service;
    }

    /**
     * @param \JsonMapper $mapper
     */
    protected function setMapper(\JsonMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function initialize()
    {
        $this->setService($this->di->getAppServices('imageService'));
        $this->setMapper($this->di->get('jsonMapper'));
    }

    /**
     * @Post('/upload')
     * @param UploadRequestHandler $request
     */
    public function uploadAction(UploadRequestHandler $request)
    {
        try {
            /** @var UploadRequestHandler $request */
            $request = $this->mapper->map($this->request->getPost(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $image = call_user_func_array([$this->service, 'upload'], $request->toArray());
            $request->successRequest($image);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Delete('/{id:[0-9a-zA-Z]{7}}')
     * @param $id
     * @param DeleteRequestHandler $request
     */
    public function deleteAction($id, DeleteRequestHandler $request)
    {
        try {
            /** @var DeleteRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->service->delete(
                $request->productId,
                $id,
                $request->albumId
            );
            $request->successRequest('Deleted');
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @param MakeMainImageRequestHandler $request
     * @Put('/makeMain/{id:[0-9a-zA-Z]{7}}')
     */
    public function makeMainAction($id, MakeMainImageRequestHandler $request)
    {
        try {
            /** @var MakeMainImageRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            $this->service->makeMainImage($id, $request->productId);
            http_response_code(204);
            $this->response->send();
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @param UpdateOrderRequestHandler $request
     * @Put('/order/{id:[0-9a-zA-Z]{7}}')
     */
    public function updateOrderAction($id, UpdateOrderRequestHandler $request)
    {
        try {
            /** @var UpdateOrderRequestHandler $request */
            $request = $this->mapper->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->service->updateOrder($request->productId, $id, $request->order);
            $request->successRequest(null, 204);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
