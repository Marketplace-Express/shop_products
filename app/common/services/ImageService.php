<?php
/**
 * User: Wajdi Jurry
 * Date: 05/04/19
 * Time: 02:46 م
 */

namespace app\common\services;


use app\common\enums\AccessLevelsEnum;
use app\common\exceptions\OperationNotPermitted;
use app\common\services\cache\ImagesCache;
use Mechpave\ImgurClient\Entity\ImageInterface;
use Phalcon\Di;
use Phalcon\Http\Request\File;
use app\common\repositories\ImageRepository;
use app\common\repositories\ProductRepository;
use app\common\services\cache\ProductCache;
use app\common\utils\ImgurUtil;
use app\common\exceptions\OperationFailed;
use app\common\exceptions\NotFound;

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
     * @throws NotFound
     * @throws OperationFailed
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

        /** @var ImageInterface $uploaded */
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
                $albumId,
                $uploaded->getType(),
                $uploaded->getWidth(),
                $uploaded->getHeight(),
                $uploaded->getSize(),
                $uploaded->getDeleteHash(),
                $uploaded->getName(),
                $uploaded->getLink()
            );

            $this->getRepository()->saveSizes($image, $image->imageLink);

            $data = $image->toApiArray();

            ImagesCache::getInstance()->set($productId, $data);
        }
        unlink($config->uploadDir . $newImageName);
        return $data;
    }

    /**
     * @param string $productId
     * @param string $imageId
     * @param string $albumId
     * @param int $accessLevel
     * @throws OperationFailed
     * @throws NotFound
     * @throws OperationNotPermitted
     * @throws \RedisException
     * @throws \Exception
     */
    public function delete(string $productId, string $imageId, string $albumId, int $accessLevel = AccessLevelsEnum::NORMAL_USER): void
    {
        if ($accessLevel < 1) {
            throw new OperationNotPermitted('Not allowed action');
        }
        if ($this->getRepository()->delete($imageId, $albumId, $productId)) {
            ImagesCache::getInstance()->invalidate($productId, $imageId);
        }
    }
}
