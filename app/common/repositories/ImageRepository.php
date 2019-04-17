<?php
/**
 * User: Wajdi Jurry
 * Date: 06/04/19
 * Time: 12:01 م
 */

namespace Shop_products\Repositories;


use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Models\ProductImages;

class ImageRepository
{
    /** @var ProductImages */
    private $model;

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