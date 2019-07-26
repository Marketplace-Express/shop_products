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
use Shop_products\Repositories\ProductRepository;
use Shop_products\Services\Cache\ProductCache;
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
        $simpleProductData = ProductRepository::getInstance()->getColumnsForProduct($productId, [
            'productCategoryId', 'productVendorId', 'productAlbumId'
        ]);

        if ($albumId != $simpleProductData['productAlbumId']) {
            throw new \Exception('incorrect product album id', 400);
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

            $this->getRepository()->saveSizes($image->imageId, $image->imageLink);

            ProductCache::getInstance()->invalidateCache(
                $simpleProductData['productVendorId'],
                $simpleProductData['productCategoryId'],
                [$productId]
            );
        }
        unlink($config->uploadDir . $newImageName);
        return $data;
    }
}