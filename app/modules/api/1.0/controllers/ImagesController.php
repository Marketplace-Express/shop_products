<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace app\modules\api\controllers;


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
    private $service;

    /**
     * @throws Exception
     */
    public function initialize()
    {
        $albumId = $this->request->getPost('albumId');
        $productId = $this->request->getPost('productId');
        if (!isset($albumId) || !$this->getUuidUtil()->isValid($productId)) {
            throw new Exception('Invalid album Id or product Id');
        }
        $this->service = new ImageService();
    }

    /**
     * @return ImageService
     */
    public function getService(): ImageService
    {
        return $this->service;
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
                $this->queryStringToObject($this->request->getPost()),
                new UploadRequestHandler()
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
}