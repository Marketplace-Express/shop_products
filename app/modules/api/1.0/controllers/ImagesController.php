<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


use app\common\requestHandler\image\DeleteRequestHandler;
use Exception;
use app\common\controllers\BaseController;
use app\common\requestHandler\image\UploadRequestHandler;
use app\common\services\ImageService;
use Throwable;

/**
 * Class ImagesController
 * @package app\modules\api\controllers
 * @RoutePrefix('/api/1.0/images')
 */
class ImagesController extends BaseController
{
    /**
     * @throws Exception
     */
    public function initialize()
    {
        $albumId = $this->request->get('albumId');
        $productId = $this->request->get('productId');
        if (!isset($albumId) || !$this->getUuidUtil()->isValid($productId)) {
            throw new Exception('Invalid album Id or product Id');
        }
    }

    /**
     * @return ImageService
     */
    public function getService(): ImageService
    {
        return new ImageService();
    }

    /**
     * @Post('/upload')
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function uploadAction()
    {
        try {
            /** @var UploadRequestHandler $request */
            $request = $this->getJsonMapper()->map(
                $this->request->getPost(),
                new UploadRequestHandler($this)
            );
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $image = call_user_func_array([$this->getService(), 'upload'], $request->toArray());
            $request->successRequest($image);
        } catch (Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    /**
     * @Delete('/{id:[0-9a-zA-Z]{7}}')
     * @param $id
     * @AuthMiddleware("\app\common\events\middleware\RequestMiddlewareEvent")
     */
    public function deleteAction($id)
    {
        try {
            /** @var DeleteRequestHandler $request */
            $request = $this->getJsonMapper()->map(
                $this->request->getQuery(),
                new DeleteRequestHandler($this)
            );
            if (!$request->isValid()) {
                $request->invalidRequest();
            }
            $this->getService()->delete($request->productId, $id, $request->albumId, $request->getAccessLevel());
            $request->successRequest('Deleted');
        } catch (\Throwable $exception) {
            $this->handleError($exception->getMessage(), $exception->getCode());
        }
    }
}
