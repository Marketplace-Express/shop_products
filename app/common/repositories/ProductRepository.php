<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:58 م
 */

namespace Shop_products\Repositories;

use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\ModelInterface;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Interfaces\DataSourceInterface;
use Shop_products\Models\DownloadableProduct;
use Shop_products\Models\PhysicalProduct;
use Shop_products\Collections\Product as ProductCollection;
use Shop_products\Models\Product as ProductModel;

class ProductRepository implements DataSourceInterface
{
    /**
     * @param string $type
     * @param bool $new
     * @return ProductModel|PhysicalProduct|DownloadableProduct
     * @throws \Exception
     */
    public function getModel(bool $new = false, ?string $type = null)
    {
        if (!is_null($type) && !in_array($type, ProductTypesEnums::getValues())) {
            throw new \Exception('Unknown product type', 400);
        }
        switch ($type) {
            case ProductTypesEnums::TYPE_PHYSICAL:
                $model = PhysicalProduct::model($new);
                break;
            case ProductTypesEnums::TYPE_DOWNLOADABLE:
                $model = DownloadableProduct::model($new);
                break;
            default:
                $model = ProductModel::model($new);
        }
        return $model;
    }

    /**
     * @param bool $new
     * @return ProductCollection
     */
    public function getCollection(bool $new = false): ProductCollection
    {
        return ProductCollection::model($new);
    }

    /**
     * @return ProductRepository
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Get product by id
     *
     * @param string $productId
     * @param bool $editMode
     * @return ProductModel|ModelInterface
     * @throws \Exception
     */
    public function getById(string $productId, bool $editMode = false)
    {
        $query = $this->getModel()::query()
            ->andWhere('productId = :productId:')
            ->bind(['productId' => $productId]);

        if ($editMode) {
            $query->inWhere('isPublished', [true, false]);
        }

        $query = $query->execute();
        if (!$query->count()) {
            throw new \Exception('Product not found or maybe deleted', 404);
        }
        return $query->getFirst();
    }

    /**
     * Get products by category id
     *
     * @param string $categoryId
     * @param string $vendorId
     * @param bool $editMode
     * @return array|null
     *
     * @throws \Exception
     */
    public function getByCategoryId(string $categoryId, string $vendorId, bool $editMode = false): ?array
    {
        $query = $this->getModel()::query()
            ->andWhere('productCategoryId = :productCategoryId:')
            ->andWhere('productVendorId = :productVendorId:');

        if ($editMode) {
            $query->inWhere('isPublished', [true, false]);
        }

        $query->bind([
            'productCategoryId' => $categoryId,
            'productVendorId' => $vendorId
        ]);

        // execute query
        $products = $query->execute();

        $result = [];
        /** @var ProductModel $product */
        foreach ($products as $product) {
            $result[] = $product->toApiArray();
        }

        return $result;
    }

    /**
     * Get products by vendor id
     *
     * @param string $vendorId
     * @param bool $editMode
     * @return array|null
     *
     * @throws \Exception
     */
    public function getByVendorId(string $vendorId, bool $editMode = false): ?array
    {
        $query = $this->getModel()::query()
            ->andWhere('productVendorId = :productVendorId:');

        if ($editMode) {
            $query->inWhere('isPublished', [true, false]);
        }

        // Map result to model
        $query->setModelName(ProductModel::class);

        $resultSet = $query->bind(['productVendorId' => $vendorId])->execute();

        $result = [];
        /** @var ProductModel $item */
        foreach ($resultSet->toArray() as $item) {
            $result[] = $item->toApiArray();
        }
        return $result;
    }

    /**
     * Create new product
     *
     * @param array $data
     * @return ProductModel
     *
     * @throws ArrayOfStringsException
     * @throws \Phalcon\Mvc\Collection\Exception
     * @throws \Exception
     */
    public function create(array $data): ProductModel
    {
        $productCollectionData = [];
        if (!empty($data['productKeywords']) || !empty($data['productSegments'] || !empty($data['productDimensions']))) {
            $productCollectionData = [
                'dimensions' => $data['productDimensions'] ?? null,
                'keywords' => $data['productKeywords'] ?? null,
                'segments' => $data['productSegments'] ?? null,
                'product_id' => $data['productId']
            ];
            unset($data['productKeywords'], $data['productSegments'], $data['productDimensions']);
        }

        $productModel = $this->getModel(true, $data['productType']);

        if (!$productModel->save($data, $productModel::getWhiteList())) {
            throw new ArrayOfStringsException($productModel->getMessages(), 400);
        }

        if (!empty($productCollectionData)) {
            $productCollection = $this->getCollection(true);
            $productCollection->setAttributes($productCollectionData);
            if (!$productCollection->save()) {
                throw new ArrayOfStringsException($productCollection->getMessages(), 400);
            }
            unset($productCollectionData['product_id']);
            foreach ($productCollection as $field => $value) {
                $productModel->writeAttribute($field, $value);
            }
        }

        return $productModel;
    }

    /**
     * Update product
     *
     * @param string $productId
     * @param array $data
     * @return ProductModel
     * @throws ArrayOfStringsException
     * @throws \Exception
     */
    public function update(string $productId, array $data)
    {
        $product = $this->getById($productId, true);
        if (!$product->update($data, $product::getWhiteList())) {
            throw new ArrayOfStringsException($product->getMessages(), 400);
        }
        return $product;
    }

    /**
     * Delete product
     *
     * @param string $productId
     * @return array
     * @throws \Exception
     */
    public function delete(string $productId)
    {
        $product = $this->getById($productId, true);
        if (!$product || !$product->delete()) {
            throw new \Exception('Product not found or maybe deleted', 500);
        }
        return $product->toApiArray();
    }
}