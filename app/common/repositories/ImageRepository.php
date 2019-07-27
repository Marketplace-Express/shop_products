<?php
/**
 * User: Wajdi Jurry
 * Date: 06/04/19
 * Time: 12:01 م
 */

namespace app\common\repositories;


use app\common\exceptions\ArrayOfStringsException;
use app\common\exceptions\NotFoundException;
use app\common\models\ProductImages;
use app\common\models\ProductImagesSizes;

class ImageRepository
{
    /** @var ProductImages */
    private $model;

    /**
     * @param bool $new
     * @return \app\common\models\BaseModel|ProductImages
     */
    public function getModel(bool $new = false)
    {
        return $this->model ?? $this->model = ProductImages::model($new);
    }

    /**
     * @return ImageRepository
     */
    static public function getInstance(): ImageRepository
    {
        return new self;
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
     * @return ProductImages
     * @throws ArrayOfStringsException
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
        string $link
    )
    {
        $model = $this->getModel(true);
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
        if (!$model->save($data)) {
            throw new ArrayOfStringsException($model->getMessages(), 500);
        }
        return $model;
    }

    /**
     * @param string $imageId
     * @param string $imageLink
     * @return bool
     * @throws ArrayOfStringsException
     */
    public function saveSizes(string $imageId, string $imageLink)
    {
        $model = ProductImagesSizes::model(true);
        $imageLinkArr = explode('.', $imageLink);
        $small = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'s', $imageLinkArr[3]]);
        $big = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'b', $imageLinkArr[3]]);
        $thumb = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'t', $imageLinkArr[3]]);
        $medium = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'m', $imageLinkArr[3]]);
        $large = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'l', $imageLinkArr[3]]);
        $huge = implode('.', [$imageLinkArr[0], $imageLinkArr[1], $imageLinkArr[2].'h', $imageLinkArr[3]]);

        $model->imageId = $imageId;
        $model->small = $small;
        $model->big = $big;
        $model->thumb = $thumb;
        $model->medium = $medium;
        $model->large = $large;
        $model->huge = $huge;

        if (!$model->save()) {
            throw new ArrayOfStringsException($model->getMessages(), 400);
        }
        return true;
    }

    /**
     * @param string $imageId
     * @param string $albumId
     * @param string $productId
     * @return bool
     * @throws ArrayOfStringsException
     * @throws NotFoundException
     */
    public function delete(string $imageId, string $albumId, string $productId): bool
    {
        $image = $this->getModel()::findFirst([
            'conditions' => 'imageId = :imageId: AND imageAlbumId = :albumId: AND productId = :productId:',
            'bind' => [
                'productId' => $productId,
                'imageId' => $imageId,
                'albumId' => $albumId
            ]
        ]);

        if (!$image) {
            throw new NotFoundException('Image not found or maybe deleted');
        }

        if (!$image->delete()) {
            throw new ArrayOfStringsException($image->getMessages());
        }

        $imageSizes = new ProductImagesSizes();
        $imageVersions = $imageSizes::findFirst([
            'conditions' => 'imageId = :imageId:',
            'bind' => [
                'imageId' => $imageId
            ]
        ]);

        if ($imageVersions) {
            $imageVersions->delete();
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
        $allProductImages = $this->getModel()->find([
            'conditions' => 'productId = :productId:',
            'bind' => [
                'productId' => $productId
            ]
        ]);
        if ($allProductImages) {
            foreach ($allProductImages as $productImage) {
                $allDeleted = $productImage->delete();
            }
        }

        $imagesIds = array_column($allProductImages->toArray(), 'imageId');
        $imageSizeModel = new ProductImagesSizes();
        $allImagesVersions = $imageSizeModel::find([
            'conditions' => 'imageId IN  ({imagesIds:array})',
            'bind' => [
                'imagesIds' => $imagesIds
            ]
        ]);

        if ($allImagesVersions) {
            foreach ($allImagesVersions as $imageVersion) {
                $allDeleted = $imageVersion->delete();
            }
        }
        return $allDeleted;
    }
}