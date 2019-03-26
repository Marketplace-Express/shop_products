<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:58 م
 */

namespace Shop_products\Repositories;

use Phalcon\Mvc\ModelInterface;
use Shop_products\Enums\QueueNamesEnum;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Exceptions\NotFoundException;
use Shop_products\Interfaces\DataSourceInterface;
use Shop_products\Models\DownloadableProduct;
use Shop_products\Models\PhysicalProduct;
use Shop_products\Collections\Product as ProductCollection;
use Shop_products\Models\Product;
use Shop_products\RequestHandler\Queue\QueueRequestHandler;

class ProductRepository implements DataSourceInterface
{
    /**
     * @param bool $new
     * @return Product|PhysicalProduct|DownloadableProduct
     * @throws \Exception
     */
    public function getModel(bool $new = false)
    {
        return Product::model($new);
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
     * @param bool|null $getExtraInfo
     * @return Product|ModelInterface
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getById(string $productId, bool $editMode = false, ?bool $getExtraInfo = false)
    {
        $conditions = 'productId = :productId:';
        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        }

        /** @var Product $product */
        $product = self::getModel()::findFirst([
            'conditions' => $conditions,
            'bind' => ['productId' => $productId]
        ]);

        if (empty($product)) {
            throw new NotFoundException('product not found or maybe deleted');
        }

        $product->mapResultSet($product);

        if ($getExtraInfo) {
            $productExtraInfo = $this->getCollection()::findFirst([
                ['product_id' => $productId]
            ]);
            $product->assign($productExtraInfo->toApiArray(), null, $product::getWhiteList());
        }

        return $product;
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
        $conditions = 'productCategoryId = :productCategoryId AND productVendorId = :productVendorId:';

        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        }

        $products = $this->getModel()::find([
            'conditions' => $conditions,
            'bind' => [
                'productCategoryId' => $categoryId,
                'productVendorId' => $vendorId
            ]
        ]);

        $result = [];
        /** @var Product $product */
        foreach ($products as $product) {
            $product->mapResultSet($product);
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
        $conditions = 'productVendorId = :productVendorId:';

        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        }

        $products = $this->getModel()::find([
            'conditions' => $conditions,
            'bind' => ['productVendorId' => $vendorId]
        ]);

        $result = [];
        /** @var Product $item */
        foreach ($products as $product) {
            $product->mapResultSet($product);
            $result[] = $product->toApiArray();
        }

        return $result;
    }

    /**
     * Create new product
     *
     * @param array $data
     * @return Product
     *
     * @throws ArrayOfStringsException
     * @throws \Phalcon\Mvc\Collection\Exception
     * @throws \Exception
     */
    public function create(array $data): Product
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

        $productModel = $this->getModel(true)->detectModelType($data['productType']);

        if (!$productModel->create($data, $productModel::getWhiteList())) {
            throw new ArrayOfStringsException($productModel->getMessages(), 400);
        }

        if (!empty($productCollectionData)) {
            $productCollection = $this->getCollection(true);
            $productCollection->setAttributes($productCollectionData);
            if (!$productCollection->save()) {
                throw new ArrayOfStringsException($productCollection->getMessages(), 400);
            }
            unset($productCollectionData['product_id']);
            foreach ($productCollection->toApiArray() as $field => $value) {
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
     * @return Product
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
            throw new \Exception('Product not found or maybe deleted', 404);
        }
        (new QueueRequestHandler(QueueRequestHandler::REQUEST_TYPE_ASYNC))
            ->setQueueName(QueueNamesEnum::PRODUCT_ASYNC_QUEUE)
            ->setService('product')
            ->setMethod('deleteExtraInfo')
            ->setData([
                'product_id' => $product->productId
            ])->sendAsync();
        return $product->toApiArray();
    }
}