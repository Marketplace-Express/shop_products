<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 07:32 م
 */

namespace Shop_products\Modules\Api\Controllers;


use Exception;
use Shop_products\Controllers\BaseController;
use Shop_products\RequestHandler\Image\UploadRequestHandler;
use Shop_products\Services\ImageService;
use Throwable;

/**
 * Class ImagesController
 * @package Shop_products\Modules\Api\Controllers
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
        if (!isset($albumId) || !isset($productId)) {
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