<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


use app\common\requestHandler\image\{
    DeleteRequestHandler,
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
    private $albumId;
    private $productId;

    /**
     * @throws \Exception
     */
    public function initialize()
    {
        $albumId = $this->request->get('albumId');
        $productId = $this->request->get('productId');
        if (!isset($albumId) || !$this->getUuidUtil()->isValid($productId)) {
            throw new \Exception('Invalid album Id or product Id');
        }
        $this->albumId = $albumId;
        $this->productId = $productId;
    }

    /**
     * @Post('/upload')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     * @param UploadRequestHandler $request
     */
    public function uploadAction(UploadRequestHandler $request)
    {
        try {
            /** @var UploadRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getPost(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $image = call_user_func_array([$this->di->getAppServices('imageService'), 'upload'], $request->toArray());
            $request->successRequest($image);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Delete('/{id:[0-9a-zA-Z]{7}}')
     * @param $id
     * @param DeleteRequestHandler $request
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function deleteAction($id, DeleteRequestHandler $request)
    {
        try {
            /** @var DeleteRequestHandler $request */
            $request = $this->di->get('jsonMapper')->map($this->request->getQuery(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->di->getAppServices('imageService')->delete(
                $request->productId,
                $id,
                $request->albumId,
                $request->getAccessLevel()
            );
            $request->successRequest('Deleted');
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param $id
     * @Put('/makeMain/{id:[0-9a-zA-Z]{7}}')
     */
    public function makeMainAction($id)
    {
        try {
            $this->di->getAppServices('imageService')->makeMainImage($id, $this->productId);
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
            $request = $this->di->get('jsonMapper')->map($this->request->getJsonRawBody(), $request);
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->di->getAppServices('imageService')->updateOrder($this->productId, $id, $request->order);
            $request->successRequest(null, 204);
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
