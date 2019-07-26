<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:58 م
 */

namespace Shop_products\Repositories;

use Phalcon\Mvc\Collection\Exception;
use Phalcon\Mvc\Model\Resultset;
use Shop_products\Enums\MongoQueryOperatorsEnum;
use Shop_products\Enums\ProductTypesEnum;
use Shop_products\Enums\QueueNamesEnum;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Exceptions\NotFoundException;
use Shop_products\Interfaces\DataSourceInterface;
use Shop_products\Collections\Product as ProductCollection;
use Shop_products\Models\DownloadableProperties;
use Shop_products\Models\PhysicalProperties;
use Shop_products\Models\Product;
use Shop_products\Models\ProductImages;
use Shop_products\Models\ProductQuestions;
use Shop_products\Models\ProductRates;
use Shop_products\RequestHandler\Queue\QueueRequestHandler;

class ProductRepository implements DataSourceInterface
{
    /**
     * @param bool $new
     * @param bool $attachRelations
     * @param bool $editMode
     * @return Product
     */
    public function getModel(bool $new = false, bool $attachRelations = true, bool $editMode = false, bool $attachProperties = true)
    {
         return Product::model($new, $attachRelations, $editMode, $attachProperties);
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
     * @param array $result
     * @param bool $attachRelations
     * @param bool $returnModels
     * @return array
     */
    private function manipulateRecords(array $result, bool $attachRelations = true, bool $returnModels = false)
    {
        $products = [];
        if (!$returnModels) {
            foreach ($result as $product) {
                $basicInfo = $product->toApiArray();

                if ($attachRelations) {
                    $productArray = array_merge(
                        $basicInfo,
                        ['productImages' => ($product->productImages->productId) ? [$product->productImages->toApiArray()] : []],
                        ['productQuestions' => ($product->productQuestions->productId) ? [$product->productQuestions->toApiArray()] : []],
                        ['productRates' => ($product->productRates->productId) ? [$product->productRates->toApiArray()] : []]
                    );

                    if (array_key_exists($basicInfo['productId'], $products)) {
                        if (!empty($productArray['productImages'])) {
                            array_push($products[$basicInfo['productId']]['productImages'], array_shift($productArray['productImages']));
                        }
                        if (!empty($productArray['productQuestions'])) {
                            array_push($products[$basicInfo['productId']]['productQuestions'], array_shift($productArray['productQuestions']));
                        }
                        if (!empty($productArray['productRates'])) {
                            array_push($products[$basicInfo['productId']]['productRates'], array_shift($productArray['productRates']));
                        }
                    } else {
                        $products[$basicInfo['productId']] = $productArray;
                    }
                }
            }
        } else {
            foreach ($result as $product) {

                if (!array_key_exists($product->productId, $products)) {
                    $products[$product->productId] = $product;
                }

                $productImages = $product->productImages;
                $productQuestions = $product->productQuestions;
                $productRates = $product->productRates;

                $product->productImages = [];
                $product->productQuestions = [];
                $product->productRates = [];

                if ($attachRelations) {

                    if (!empty($productImages->productId)) {
                        $products[$product->productId]->productImages[] = $productImages;
                    }
                    if (!empty($productQuestions->productId)) {
                        $products[$product->productId]->productQuestions[] = $productQuestions;
                    }
                    if (!empty($productRates->productId)) {
                        $products[$product->productId]->productRates[] = $productRates;
                    }
                }
            }
        }
        return array_values($products);
    }

    /**
     * @param string $productId
     * @param array $columns
     * @param bool $editMode
     * @return array
     * @throws NotFoundException
     */
    public function getColumnsForProduct(string $productId, array $columns, bool $editMode = false)
    {
        if (empty($columns)) {
            $columns = Product::MODEL_ALIAS.'.*';
        }

        /** @var array $product */
        $product = $this->getModel(true, false, $editMode)::findFirst([
            'columns' => $columns,
            'conditions' => 'productId = :productId: AND isDeleted = false',
            'bind' => [
                'productId' => $productId
            ],
            'hydrate' => Resultset::HYDRATE_ARRAYS
        ]);

        if (!$product) {
            throw new NotFoundException('product not found or maybe deleted');
        }

        return $product;
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
     * @param bool $attachProperties
     * @return Product
     *
     * @throws NotFoundException
     */
    public function getById(
        string $productId,
        string $vendorId,
        bool $editMode = false,
        bool $getExtraInfo = true,
        bool $attachRelations = true,
        bool $returnModel = false,
        bool $attachProperties = true
    )
    {
        $conditions = 'productId = :productId: AND productVendorId = :vendorId:';
        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        } else {
            $conditions .= ' AND isPublished = true';
        }

        $product = $this->getModel(false, $attachRelations, $editMode, $attachProperties)::findFirst([
            'conditions' => $conditions,
            'bind' => [
                'productId' => $productId,
                'vendorId' => $vendorId
            ]
        ]);

        if (!$product) {
            throw new NotFoundException('product not found or maybe deleted');
        }

        if ($getExtraInfo) {
            $productExtraInfo = $this->getCollection()::findFirst([
                ['product_id' => $productId]
            ]);
            if (!empty($productExtraInfo)) {
                $product->assign($productExtraInfo->toApiArray());
            }
        }

        return $product;
    }

    /**
     * Get products by category id
     *
     * @param string $categoryId
     * @param string $vendorId
     * @param null|int $limit
     * @param null|int $page
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
        int $limit = Product::DEFAULT_LIMIT,
        int $page = 1,
        bool $editMode = false,
        bool $attachRelations = true,
        bool $getExtraInfo = false,
        bool $returnModels = false): ?array
    {
        $conditions = 'productCategoryId = :productCategoryId:';
        $conditions .= ' AND productVendorId = :productVendorId:';

        if ($editMode) {
            $conditions .= ' AND isPublished IN (TRUE, FALSE)';
        } else {
            $conditions .= ' AND isPublished = TRUE';
        }

        $conditions .= ' AND isDeleted = false';

        $result = $this->getModel(
            false, $attachRelations, $editMode
        )::find([
            'conditions' => $conditions,
            'bind' => [
                'productCategoryId' => $categoryId,
                'productVendorId' => $vendorId
            ],
            'limit' => $limit,
            'offset' => ($page - 1)
        ]);

        if (!count($result)) {
            throw new \Exception('no products found', 404);
        }

        $products = [];

        if ($getExtraInfo) {
            $productsIds = array_unique(array_map(function ($product) {
                return $product['productId'];
            }, $result->toArray()));
            $productsExtraInfo = $this->getCollection()::find([
                'product_id' => [MongoQueryOperatorsEnum::OP_IN => $productsIds]
            ]);
            if (!empty($productsExtraInfo)) {
                /** @var ProductCollection $productExtraInfo */
                foreach ($productsExtraInfo as $productExtraInfo) {
                    $productIndex = array_search($productExtraInfo->product_id, $productsIds);
                    if ($productIndex === false) {
                        continue;
                    }
                    $products[] = $result->offsetGet($productIndex)->assign($productExtraInfo->toApiArray());
                }
            }
        }

        $result = [];

        foreach ($products as $product) {
            $result[] = $product->toApiArray();
        }

        return $result;
    }

    /**
     * Get products by vendor id
     *
     * @param string $vendorId
     * @param null|int $limit
     * @param null|int $page
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
        ?int $limit = Product::DEFAULT_LIMIT,
        ?int $page = 1,
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
            ],
            'limit' => $limit,
            'page' => $page
        ]);

        if (!$products->count()) {
            throw new \Exception('no products found', 404);
        }

        $products = $products->toArray();

        if ($getExtraInfo) {
            $productsIds = array_unique(array_map(function ($product) {
                return $product->{Product::MODEL_ALIAS}->productId;
            }, $products));
            $productsExtraInfo = $this->getCollection()::find([
                'product_id' => [MongoQueryOperatorsEnum::OP_IN => $productsIds]
            ]);
            if (!empty($productsExtraInfo)) {
                /** @var ProductCollection $productExtraInfo */
                foreach ($productsExtraInfo as $productExtraInfo) {
                    $productIndex = array_search($productExtraInfo->product_id, $productsIds);
                    $products[$productIndex]->{Product::MODEL_ALIAS}->assign($productExtraInfo->toApiArray());
                }
            }
        }

        $result = $this->manipulateRecords($products, $attachRelations, $returnModels);
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

        if ($data['productType'] == ProductTypesEnum::TYPE_PHYSICAL) {
            $properties = PhysicalProperties::model(true);
            $properties->productWeight = $data['productWeight'];
            $properties->productBrandId = $data['productBrandId'];
            $productModel->pp = $properties;
        } elseif ($data['productType'] == ProductTypesEnum::TYPE_DOWNLOADABLE) {
            $properties = DownloadableProperties::model(true);
            $properties->productDigitalSize = $data['productDigitalSize'];
            $productModel->dp = $properties;
        } else {
            throw new \Exception('unknown product type', 400);
        }

        if (!$productModel->create($data, $productModel::getWhiteList())) {
            throw new ArrayOfStringsException($productModel->getMessages(), 400);
        }

        $productModel->assign($properties->toApiArray(), null, Product::getWhiteList());

        if (!empty($productCollectionData)) {
            $productCollection = $this->getCollection(true);
            $productCollection->setAttributes($productCollectionData);
            if (!$productCollection->save()) {
                throw new ArrayOfStringsException($productCollection->getMessages(), 400);
            }
            unset($productCollectionData['product_id']);
            $productModel->assign($productCollection->toApiArray(), null, Product::getWhiteList());
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
        $attachProperties = false;
        if (count(array_intersect(
                    array_keys($data), PhysicalProperties::WHITE_LIST
                )
            )
            ||
            count(array_intersect(
                    array_keys($data), DownloadableProperties::WHITE_LIST
                )
            )
        ) {
            $attachProperties = true;
        }

        $product = $this->getById($productId, $vendorId, true, false, false, true, $attachProperties);

        if ($attachProperties) {
            if ($product->productType == ProductTypesEnum::TYPE_PHYSICAL) {
                $relatedModelAlias = PhysicalProperties::MODEL_ALIAS;
                $propertiesFields = array_intersect_key($data, array_flip(PhysicalProperties::WHITE_LIST));
            } else {
                $propertiesFields = array_intersect_key($data, array_flip(DownloadableProperties::WHITE_LIST));
                $relatedModelAlias = DownloadableProperties::MODEL_ALIAS;
            }
            $product->{$relatedModelAlias}->assign($propertiesFields);
        }

        if (!$product->update($data, Product::getWhiteList())) {
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