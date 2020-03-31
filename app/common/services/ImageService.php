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
use Mechpave\ImgurClient\Entity\Album;
use Mechpave\ImgurClient\Entity\ImageInterface;
use Phalcon\Di;
use Phalcon\Http\Request\File;
use app\common\repositories\ImageRepository;
use app\common\repositories\ProductRepository;
use app\common\utils\ImgurUtil;
use app\common\exceptions\OperationFailed;
use app\common\exceptions\NotFound;

class ImageService
{
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

        if (empty($simpleProductData['productAlbumId']) && !empty($album = $this->createAlbum($productId))) {
            ProductRepository::getInstance()->setAlbum($productId, $album);
            // Override product data
            $simpleProductData = [
                'productCategoryId' => $simpleProductData['productCategoryId'],
                'productVendorId' => $simpleProductData['productVendorId'],
                'productAlbumId' => $album['albumId']
            ];
            // Override sent album id
            $albumId = $album['albumId'];
        }

        if ($albumId != $simpleProductData['productAlbumId']) {
            throw new \Exception('incorrect product album id', 400);
        }

        $config = Di::getDefault()->getConfig()->application;
        $newImageName = time() . '.' . $image->getExtension();
        $image->moveTo(
            $config->uploadDir . $newImageName
        );

        /** @var ImageInterface $uploaded */
        $uploaded = Di::getDefault()->getImageUploader()
            ->uploadImage(
                $config->uploadDir . $newImageName,
                $image->getName(),
                $albumId
            );

        $data = [];
        if (!empty($uploaded)) {
            $image = ImageRepository::getInstance()->create(
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

            ImageRepository::getInstance()->saveSizes($image, $image->imageLink);

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
        if (ImageRepository::getInstance()->delete($imageId, $albumId, $productId)) {
            ImagesCache::getInstance()->invalidate($productId, $imageId);
        }
    }

    /**
     * @param string $imageId
     * @param string $productId
     * @return void
     * @throws OperationNotPermitted
     */
    public function makeMainImage(string $imageId, string $productId)
    {
        $images = array_map(function ($image) {
            return $image->toApiArray();
        }, ImageRepository::getInstance()->makeMainImage($imageId, $productId));
        ImagesCache::getInstance()->invalidateProductImages($productId);
        ImagesCache::getInstance()->bulkProductSet($productId, $images);
    }

    /**
     * @param string $productId
     * @param string $imageId
     * @param int $order
     * @throws NotFound
     * @throws OperationFailed
     */
    public function updateOrder(string $productId, string $imageId, int $order = 0)
    {
        $image = ImageRepository::getInstance()->updateOrder($imageId, $order);
        ImagesCache::getInstance()->set($productId, $image->toApiArray());
    }


    /**
     * @param string $productId
     * @return array
     */
    private function createAlbum(string $productId): array
    {
        $data = [];
        /** @var Album $album */
        $album = Di::getDefault()->getImageUploader()->createAlbum($productId);
        if (!empty($album)) {
            $data = [
                'albumId' => $album->getAlbumId(),
                'deleteHash' => $album->getDeleteHash()
            ];
        }
        return $data;
    }
}
