<?php
/**
 * User: Wajdi Jurry
 * Date: 06/04/19
 * Time: 12:01 م
 */

namespace app\common\repositories;


use app\common\enums\ImagesTypesEnum;
use app\common\exceptions\OperationFailed;
use app\common\exceptions\NotFound;
use app\common\exceptions\OperationNotPermitted;
use app\common\models\Image;
use app\common\models\ImageSizes;
use app\common\models\RateImage;

class ImageRepository extends BaseRepository
{
    /**
     * @param bool $new
     * @return Image
     */
    public function getModel(bool $new = false): Image
    {
        return Image::model($new);
    }

    /**
     * @param string $productId
     * @param string $imageId
     * @param string $albumId
     * @param string $type
     * @param string $width
     * @param string $height
     * @param string $size
     * @param string $deleteHash
     * @param string $name
     * @param string $link
     * @param string $entity
     * @return Image
     * @throws OperationFailed
     */
    public function create(
        string $productId,
        string $imageId,
        string $albumId,
        string $type,
        string $width,
        string $height,
        string $size,
        string $deleteHash,
        string $name,
        string $link,
        string $entity
    )
    {
        $model = new Image();
        $data = [
            'imageId' => $imageId,
            'imageAlbumId' => $albumId,
            'productId' => $productId,
            'imageLink' => $link,
            'imageType' => $type,
            'imageWidth' => $width,
            'imageHeight' => $height,
            'imageSize' => $size,
            'imageDeleteHash' => $deleteHash,
            'imageName' => $name
        ];

        switch ($entity) {
            case ImagesTypesEnum::TYPE_VARIATION:
                $data['isVariationImage'] = true;
                break;
            case ImagesTypesEnum::TYPE_RATE:
                $data['isRateImage'] = true;
                break;
        }

        if (!$model->save($data)) {
            throw new OperationFailed($model->getMessages(), 500);
        }

        return $model;
    }

    /**
     * @param Image $image
     * @param string $imageLink
     * @return bool
     * @throws OperationFailed
     */
    public function saveSizes(Image $image, string $imageLink)
    {
        $sizes = new ImageSizes();
        $imageLinkArr = explode('.', $imageLink);
        $small = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'s', $imageLinkArr[3]]);
        $big = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'b', $imageLinkArr[3]]);
        $thumb = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'t', $imageLinkArr[3]]);
        $medium = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'m', $imageLinkArr[3]]);
        $large = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'l', $imageLinkArr[3]]);
        $huge = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'h', $imageLinkArr[3]]);

        $sizes->imageId = $image->imageId;
        $sizes->small = $small;
        $sizes->big = $big;
        $sizes->thumb = $thumb;
        $sizes->medium = $medium;
        $sizes->large = $large;
        $sizes->huge = $huge;

        if (!$sizes->save()) {
            throw new OperationFailed($sizes->getMessages(), 400);
        }
        $image->imagesSizes = $sizes;
        return true;
    }

    /**
     * @param string $imageId
     * @param string $albumId
     * @param string $productId
     * @return bool
     * @throws OperationFailed
     * @throws NotFound
     */
    public function delete(string $imageId, string $albumId, string $productId): bool
    {
        $image = Image::findFirst([
            'conditions' => 'imageId = :imageId: AND imageAlbumId = :albumId: AND productId = :productId:',
            'bind' => [
                'productId' => $productId,
                'imageId' => $imageId,
                'albumId' => $albumId
            ]
        ]);

        if (!$image) {
            throw new NotFound('Image not found or maybe deleted');
        }

        if (!$image->delete()) {
            throw new OperationFailed($image->getMessages());
        }

        return true;
    }

    /**
     * @param string $productId
     * @return bool
     */
    public function deleteProductImages(string $productId)
    {
        $allDeleted = false;
        $allProductImages = Image::find([
            'conditions' => 'productId = :productId:',
            'bind' => ['productId' => $productId]
        ]);

        foreach ($allProductImages as $productImage) {
            $allDeleted = $productImage->delete();
        }

        return $allDeleted;
    }

    /**
     * @param array $imagesIds
     * @throws OperationFailed
     */
    public function deleteImagesByIds(array $imagesIds)
    {
        $images = Image::find([
            'conditions' => 'imageId IN ({imagesIds:array})',
            'bind' => ['imagesIds' => $imagesIds]
        ]);

        foreach ($images as $image) {
            if (!$image->delete()) {
                throw new OperationFailed('Failed to delete image', 500);
            }
        }
    }

    /**
     * @param string $imageId
     * @param string $productId
     * @return Image[]
     * @throws OperationNotPermitted
     */
    public function makeMainImage(string $imageId, string $productId)
    {
        $productImages = Image::find([
            'conditions' => 'productId = :productId:',
            'bind' => ['productId' => $productId]
        ]);

        if (!$productImages) {
            throw new OperationNotPermitted('This product has no images');
        }

        $result = [];
        foreach ($productImages as $image) {
            if ($image->imageId === $imageId) {
                $image->update(['isMain' => true]);
            } elseif($image->isMain) {
                $image->update(['isMain' => false]);
            }
            $result[] = $image;
        }

        return $result;
    }

    /**
     * @param string $imageId
     * @param int $order
     * @return Image
     * @throws NotFound
     * @throws OperationFailed
     */
    public function updateOrder(string $imageId, int $order = 0)
    {
        $image = Image::findFirst([
            'conditions' => 'imageId = :imageId:',
            'bind' => ['imageId' => $imageId]
        ]);

        if (!$image) {
            throw new NotFound('Image not found');
        }

        if (!$image->update(['imageOrder' => $order])) {
            throw new OperationFailed($image->getMessages());
        }

        return $image;
    }

    /**
     * @param string $imageId
     * @return Image
     * @throws NotFound
     * @throws \Exception
     */
    public function getUnused(string $imageId): Image
    {
        $image = Image::findFirst([
            'conditions' => 'imageId = :imageId:',
            'bind' => ['imageId' => $imageId]
        ]);

        if (!$image) {
            throw new NotFound('image not found');
        }

        if ($image->isUsed) {
            throw new \Exception('image is already used', 403);
        }

        return $image;
    }

    /**
     * @param array $imagesIds
     * @return bool
     * @throws OperationFailed
     */
    public function markAsUsed(array $imagesIds): bool
    {
        /** @var Image[] $images */
        $images = Image::find([
            'conditions' => 'imageId IN ({imagesIds:array})',
            'bind' => ['imagesIds' => $imagesIds]
        ]);

        foreach ($images as $image) {
            if (!$image->update(['isUsed' => true])) {
                throw new OperationFailed($image->getMessages());
            }
        }

        return true;
    }
}
