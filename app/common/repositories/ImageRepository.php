<?php
/**
 * User: Wajdi Jurry
 * Date: 06/04/19
 * Time: 12:01 م
 */

namespace Shop_products\Repositories;


use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Models\ProductImages;
use Shop_products\Models\ProductImagesSizes;

class ImageRepository
{
    /** @var ProductImages */
    private $model;

    /**
     * @param bool $new
     * @return \Shop_products\Models\BaseModel|ProductImages
     */
    public function getModel(bool $new = false)
    {
        return $this->model ?? $this->model = ProductImages::model($new);
    }

    /**
     * @param string $productId
     * @param string $imageId
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
     * @param string $productId
     * @return bool
     * @throws ArrayOfStringsException
     */
    public function delete(string $imageId, string $productId): bool
    {
        $image = $this->getModel()::findFirst([
            'conditions' => 'imageId = :imageId: AND productId = :productId:',
            'bind' => [
                'productId' => $productId,
                'imageId' => $imageId
            ]
        ]);

        if (!$image->delete()) {
            throw new ArrayOfStringsException($image->getMessages(), 500);
        }
        return true;
    }
}