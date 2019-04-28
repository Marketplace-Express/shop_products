<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:58 م
 */

namespace Shop_products\Repositories;

use Phalcon\Mvc\Collection\Exception;
use Phalcon\Mvc\ModelInterface;
use Shop_products\Enums\MongoQueryOperatorsEnum;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Enums\QueueNamesEnum;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Exceptions\NotFoundException;
use Shop_products\Interfaces\DataSourceInterface;
use Shop_products\Collections\Product as ProductCollection;
use Shop_products\Models\DownloadableProduct;
use Shop_products\Models\PhysicalProduct;
use Shop_products\Models\Product;
use Shop_products\RequestHandler\Queue\QueueRequestHandler;

class ProductRepository implements DataSourceInterface
{
    /**
     * @param bool $new
     * @param bool $attachRelations
     * @param bool $editMode
     * @return Product
     */
    public function getModel(bool $new = false, bool $attachRelations = true, bool $editMode = false)
    {
         return Product::model($new, $attachRelations, $editMode);
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
     * @param string $productId
     * @return mixed
     * @throws \Exception
     */
    public function isExists(string $productId)
    {
        return $this->getModel(true)::count([
            'conditions' => 'productId = :productId:',
            'bind' => ['productId' => $productId]
        ]);
    }

    /**
     * @param array $products
     */
    private function attachDownloadableProperties(array &$products): void
    {
        if (empty($products)) {
            return;
        }

        $productsIds = [];

        /** @var Product $product */
        foreach ($products as $index => $product) {
            if ($product->productType == ProductTypesEnums::TYPE_DOWNLOADABLE) {
                $productsIds[$index] = $product->productId;
            }
        }

        if (!count($productsIds)) {
            return;
        }

        $properties = DownloadableProduct::find([
            'conditions' => 'productId IN ({productsIds:array})',
            'bind' => ['productsIds' => array_values($productsIds)]
        ]);

        if (count($properties)) {
            /** @var DownloadableProduct $property */
            foreach ($properties as $property) {
                $productIndex = array_search($property->productId, $productsIds);
                /** @var Product $product */
                $product = $products[$productIndex];
                $product->assign($property->toApiArray(), null, DownloadableProduct::WHITE_LIST);
            }
        }
    }

    /**
     * @param array $products
     */
    private function attachPhysicalProperties(array &$products): void
    {
        if (empty($products)) {
            return;
        }

        $productsIds = [];

        /** @var Product $product */
        foreach ($products as $index => $product) {
            if ($product->productType == ProductTypesEnums::TYPE_PHYSICAL) {
                $productsIds[$index] = $product->productId;
            }
        }

        if (!count($productsIds)) {
            return;
        }

        $properties = PhysicalProduct::find([
            'conditions' => 'productId IN ({productsIds:array})',
            'bind' => ['productsIds' => array_values($productsIds)]
        ]);

        if (count($properties)) {
            /** @var PhysicalProduct $property */
            foreach ($properties as $property) {
                $productIndex = array_search($property->productId, $productsIds);
                /** @var Product $product */
                $product = $products[$productIndex];
                $product->assign($property->toApiArray(), null, PhysicalProduct::WHITE_LIST);
            }
        }
    }

    /**
     * Get product by id
     *
     * @param string $productId
     * @param string $vendorId
     * @param bool $editMode
     * @param bool|null $getExtraInfo
     * @param bool $attachRelations
     * @param bool $returnModel
     * @return Product|ModelInterface|array
     *
     * @throws NotFoundException
     */
    public function getById(
        string $productId,
        string $vendorId,
        bool $editMode = false,
        bool $getExtraInfo = true,
        bool $attachRelations = true,
        bool $returnModel = false
    )
    {
        $conditions = 'productId = :productId: AND productVendorId = :vendorId:';
        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        } else {
            $conditions .= ' AND isPublished = true';
        }

        /** @var Product $product */
        $product = $this->getModel(true, $attachRelations, $editMode)::findFirst([
            'conditions' => $conditions,
            'bind' => ['productId' => $productId, 'vendorId' => $vendorId]
        ]);

        if (empty($product)) {
            throw new NotFoundException('product not found or maybe deleted');
        }

        if ($product->productType == ProductTypesEnums::TYPE_PHYSICAL) {
            $whitelist = PhysicalProduct::getWhiteList();
        } elseif ($product->productType == ProductTypesEnums::TYPE_DOWNLOADABLE) {
            $whitelist = DownloadableProduct::getWhiteList();
        }

        if ($getExtraInfo) {
            $productExtraInfo = $this->getCollection()::findFirst([
                ['product_id' => $productId]
            ]);
            if (!empty($productExtraInfo)) {
                $product->assign($productExtraInfo->toApiArray(), null, $whitelist);
            }
        }

        $product = [$product];

        $this->attachDownloadableProperties($product);
        $this->attachPhysicalProperties($product);

        $product = array_shift($product);

        return $returnModel ? $product : $product->toApiArray();
    }

    /**
     * Get products by category id
     *
     * @param string $categoryId
     * @param string $vendorId
     * @param bool $editMode
     * @param bool $attachRelations
     * @param bool $getExtraInfo
     * @param bool $returnModels
     * @return array|null
     *
     * @throws \Exception
     */
    public function getByCategoryId(
        string $categoryId,
        string $vendorId,
        bool $editMode = false,
        bool $attachRelations = true,
        bool $getExtraInfo = false,
        bool $returnModels = false): ?array
    {
        $conditions = 'productCategoryId = :productCategoryId:';
        $conditions .= ' AND productVendorId = :productVendorId:';

        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        } else {
            $conditions .= ' AND isPublished = true';
        }

        $products = $this->getModel(false, $attachRelations, $editMode)::find([
            'conditions' => $conditions,
            'bind' => [
                'productCategoryId' => $categoryId,
                'productVendorId' => $vendorId
            ]
        ]);

        if (!$products->count()) {
            throw new \Exception('no products found', 404);
        }

        $result = [];

        if ($getExtraInfo) {
            $productsIds = array_column($products->toArray(), 'productId');
            $productsExtraInfo = $this->getCollection()::find([
                'product_id' => [MongoQueryOperatorsEnum::OP_IN => $productsIds]
            ]);
            if (!empty($productsExtraInfo)) {
                /** @var ProductCollection $productExtraInfo */
                foreach ($productsExtraInfo as $productExtraInfo) {
                    $productIndex = array_search($productExtraInfo->product_id, $productsIds);
                    $product = $products[$productIndex];
                    if ($product->productType == ProductTypesEnums::TYPE_PHYSICAL) {
                        $whitelist = PhysicalProduct::getWhiteList();
                    } elseif ($product->productType == ProductTypesEnums::TYPE_DOWNLOADABLE) {
                        $whitelist = DownloadableProduct::getWhiteList();
                    }
                    $product->assign($productExtraInfo->toApiArray(), null, $whitelist);
                    $result[] = $product;
                }
            }
        } else {
            foreach ($products as $product) {
                $result[] = $product;
            }
        }

        $this->attachPhysicalProperties($result);
        $this->attachDownloadableProperties($result);

        if (!$returnModels) {
            foreach ($result as &$product) {
                $product = $product->toApiArray();
            }
        }
        return $result;
    }

    /**
     * Get products by vendor id
     *
     * @param string $vendorId
     * @param bool $editMode
     * @param bool $attachRelations
     * @param bool $getExtraInfo
     * @param bool $returnModels
     * @return array|null
     *
     * @throws \Exception
     */
    public function getByVendorId(
        string $vendorId,
        bool $editMode = false,
        bool $attachRelations = true,
        bool $getExtraInfo = false,
        bool $returnModels = false): ?array
    {
        $conditions = 'productVendorId = :productVendorId:';

        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        } else {
            $conditions .= ' AND isPublished = true';
        }

        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        } else {
            $conditions .= ' AND isPublished = true';
        }

        $products = $this->getModel(false, $attachRelations, $editMode)::find([
            'conditions' => $conditions,
            'bind' => [
                'productVendorId' => $vendorId
            ]
        ]);

        if (!$products->count()) {
            throw new \Exception('no products found', 404);
        }

        $result = [];

        if ($getExtraInfo) {
            $productsIds = array_column($products->toArray(), 'productId');
            $productsExtraInfo = $this->getCollection()::find([
                'product_id' => [MongoQueryOperatorsEnum::OP_IN => $productsIds]
            ]);
            if (!empty($productsExtraInfo)) {
                /** @var ProductCollection $productExtraInfo */
                foreach ($productsExtraInfo as $productExtraInfo) {
                    $productIndex = array_search($productExtraInfo->product_id, $productsIds);
                    $product = $products[$productIndex];
                    if ($product->productType == ProductTypesEnums::TYPE_PHYSICAL) {
                        $whitelist = PhysicalProduct::getWhiteList();
                    } elseif ($product->productType == ProductTypesEnums::TYPE_DOWNLOADABLE) {
                        $whitelist = DownloadableProduct::getWhiteList();
                    }
                    $product->assign($productExtraInfo->toApiArray(), null, $whitelist);
                    $result[] = $product;
                }
            }
        } else {
            foreach ($products as $product) {
                $result[] = $product;
            }
        }

        $this->attachPhysicalProperties($result);
        $this->attachDownloadableProperties($result);

        if (!$returnModels) {
            foreach ($result as &$product) {
                $product = $product->toApiArray();
            }
        }

        return $result;
    }

    /**
     * Create new product
     *
     * @param array $data
     * @return array
     *
     * @throws ArrayOfStringsException
     * @throws Exception
     * @throws \Exception
     */
    public function create(array $data): array
    {
        $productCollectionData = [];
        if (!empty($data['productKeywords']) || !empty($data['productSegments'] || !empty($data['productPackageDimensions']))) {
            $productCollectionData = [
                'packageDimensions' => $data['productPackageDimensions'] ?? null,
                'keywords' => $data['productKeywords'] ?? null,
                'segments' => $data['productSegments'] ?? null,
                'product_id' => $data['productId']
            ];
            unset($data['productKeywords'], $data['productSegments'], $data['productPackageDimensions']);
        }

        $productModel = $this->getModel(true, true, true);

        if ($data['productType'] == ProductTypesEnums::TYPE_PHYSICAL) {
            $properties = PhysicalProduct::model(true);
            $properties->productWeight = $data['productWeight'];
            $properties->productBrandId = $data['productBrandId'];
            $productModel->physicalProperties = $properties;
        } elseif ($data['productType'] == ProductTypesEnums::TYPE_DOWNLOADABLE) {
            $properties = DownloadableProduct::model(true);
            $properties->productDigitalSize = $data['productDigitalSize'];
            $productModel->downloadableProperties = $properties;
        } else {
            throw new \Exception('unknown product type', 400);
        }

        if (!$productModel->create($data, $productModel::getWhiteList())) {
            throw new ArrayOfStringsException($productModel->getMessages(), 400);
        }

        $productModel->assign($properties->toApiArray(), null);

        if (!empty($productCollectionData)) {
            $productCollection = $this->getCollection(true);
            $productCollection->setAttributes($productCollectionData);
            if (!$productCollection->save()) {
                throw new ArrayOfStringsException($productCollection->getMessages(), 400);
            }
            unset($productCollectionData['product_id']);
            $productModel->assign($productCollection->toApiArray(), null);
        }
        return $productModel->toApiArray();
    }

    /**
     * Update product
     *
     * @param string $productId
     * @param string $vendorId
     * @param array $data
     * @return array
     * @throws ArrayOfStringsException
     * @throws NotFoundException
     */
    public function update(string $productId, string $vendorId, array $data): array
    {
        $product = $this->getById($productId, $vendorId, true, true, true, true);
        if (!$product->update($data, $product::getWhiteList())) {
            throw new ArrayOfStringsException($product->getMessages(), 400);
        }
        return $product->toApiArray();
    }

    /**
     * Delete product
     *
     * @param string $productId
     * @param string $vendorId
     * @return array
     * @throws ArrayOfStringsException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function delete(string $productId, string $vendorId)
    {
        $product = $this->getById($productId, $vendorId, true, false, false, true);
        if (!$product || !$product->delete()) {
            throw new \Exception('Product not found or maybe deleted', 404);
        }

        (new QueueRequestHandler(QueueRequestHandler::REQUEST_TYPE_ASYNC))
            ->setQueueName(QueueNamesEnum::PRODUCT_ASYNC_QUEUE)
            ->setService('products')
            ->setMethod('deleteExtraInfo')
            ->setData([
                'product_id' => $product->productId
            ])->sendAsync();

        return $product->toApiArray();
    }
}