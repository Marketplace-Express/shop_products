<?php
/**
 * User: Wajdi Jurry
 * Date: 05/04/19
 * Time: 02:46 م
 */

namespace Shop_products\Services;


use Phalcon\Di;
use Phalcon\Http\Request\File;
use Shop_products\Exceptions\NotFoundException;
use Shop_products\Repositories\ImageRepository;
use Shop_products\Utils\ImgurUtil;

class ImageService
{
    /** @var ImageRepository */
    private $imageRepository;

    /** @var ProductsService */
    private $productsService;

    /**
     * @return ProductsService
     */
    public function getProductsService(): ProductsService
    {
        return $this->productsService ?? $this->productsService = new ProductsService();
    }

    public function getRepository(): ImageRepository
    {
        return $this->imageRepository ??
            $this->imageRepository = new ImageRepository();
    }

    /**
     * @param File $image
     * @param string $albumId
     * @param string $productId
     * @return array
     * @throws NotFoundException
     * @throws \Shop_products\Exceptions\ArrayOfStringsException
     * @throws \Exception
     */
    public function upload(File $image, string $albumId, string $productId)
    {
        if (!$this->getProductsService()->checkProductExistence($productId)) {
            throw new NotFoundException('product not found or maybe deleted');
        }

        $config = Di::getDefault()->getConfig()->application;
        $newImageName = time() . '.' . $image->getExtension();
        $image->moveTo(
            $config->uploadDir . $newImageName
        );
        $uploaded = (new ImgurUtil())
            ->uploadImage(
                $config->uploadDir . $newImageName,
                $image->getName(),
                $albumId
            );

        $data = [];
        if (!empty($uploaded)) {
            $image = $this->getRepository()->create(
                $productId,
                $uploaded->getImageId(),
                $uploaded->getType(),
                $uploaded->getWidth(),
                $uploaded->getHeight(),
                $uploaded->getSize(),
                $uploaded->getDeleteHash(),
                $uploaded->getName(),
                $uploaded->getLink()
            );
            $data = $image->toApiArray();
        }
        unlink($config->uploadDir . $newImageName);
        return $data;
    }
}