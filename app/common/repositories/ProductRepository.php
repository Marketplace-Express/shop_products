<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:58 م
 */

namespace app\common\repositories;

use Phalcon\Mvc\Collection\Exception;
use Phalcon\Mvc\Model\Resultset;
use app\common\enums\MongoQueryOperatorsEnum;
use app\common\enums\ProductTypesEnum;
use app\common\exceptions\OperationFailed;
use app\common\exceptions\NotFound;
use app\common\interfaces\DataSourceInterface;
use app\common\collections\Product as ProductCollection;
use app\common\models\DownloadableProperties;
use app\common\models\PhysicalProperties;
use app\common\models\Product;

class ProductRepository implements DataSourceInterface
{
    /**
     * @param bool $new
     * @param bool $attachRelations
     * @param bool $editMode
     * @return Product
     */
    public function getModel(bool $new = false, bool $attachRelations = false, bool $editMode = false, bool $attachProperties = true)
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
     * @param array $columns
     * @param bool $editMode
     * @return array
     * @throws NotFound
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
            throw new NotFound('product not found or maybe deleted');
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
     * @throws NotFound
     */
    public function getById(
        string $productId,
        string $vendorId,
        bool $editMode = false,
        bool $getExtraInfo = true,
        bool $attachRelations = false,
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
            throw new NotFound('product not found or maybe deleted');
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
     * @return array|null
     *
     * @throws \Exception
     */
    public function getByIdentifier(
        string $vendorId,
        ?string $categoryId = null,
        int $limit = 30,
        int $page = 1,
        bool $editMode = false,
        bool $attachRelations = true,
        bool $getExtraInfo = false): ?array
    {
        $binds = [];

        $conditions = 'productVendorId = :productVendorId:';

        if ($categoryId) {
            $conditions .= ' AND productCategoryId = :productCategoryId:';
            $binds['productCategoryId'] = $categoryId;
        }

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
            'bind' => array_merge([
                'productVendorId' => $vendorId
            ], $binds),
            'limit' => $limit,
            'offset' => ($page - 1) * $limit,
            'order' => 'createdAt DESC'
        ]);

        $products = [];

        if ($result && $getExtraInfo) {
            $productsIds = array_unique(array_map(function ($product) {
                return $product['productId'];
            }, $result->toArray()));
            $productsExtraInfo = $this->getCollection()::find([
                'conditions' => [
                    'product_id' => [MongoQueryOperatorsEnum::OP_IN => $productsIds]
                ]
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

    private function getProductCollectionData(array &$data)
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

            if($data['productType'] == ProductTypesEnum::TYPE_DOWNLOADABLE) {
                unset($productCollectionData['productPackageDimensions']);
            }
        }
        return $productCollectionData;
    }

    /**
     * Create new product
     *
     * @param array $data
     * @return array
     *
     * @throws OperationFailed
     * @throws Exception
     * @throws \Exception
     */
    public function create(array $data): array
    {
        $productCollectionData = $this->getProductCollectionData($data);
        $productModel = $this->getModel(true, true, true);

        if ($data['productType'] == ProductTypesEnum::TYPE_PHYSICAL) {
            $properties = PhysicalProperties::model(true);
            $properties->productWeight = $data['productWeight']->amount;
            $properties->productWeightUnit = $data['productWeight']->unit;
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
            throw new OperationFailed($productModel->getMessages(), 400);
        }

        $productModel->assign($properties->toApiArray(), null, Product::getWhiteList());

        if (!empty($productCollectionData)) {
            $productCollection = $this->getCollection(true);
            $productCollection->setAttributes($productCollectionData);
            if (!$productCollection->save()) {
                throw new OperationFailed($productCollection->getMessages(), 400);
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
     * @throws OperationFailed
     * @throws NotFound
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
            throw new OperationFailed($product->getMessages(), 400);
        }
        return $product->toApiArray();
    }

    /**
     * Delete product
     *
     * @param string $productId
     * @param string $vendorId
     * @return array
     * @throws OperationFailed
     * @throws NotFound
     * @throws \Exception
     */
    public function delete(string $productId, string $vendorId)
    {
        $product = $this->getById($productId, $vendorId, true, false, false, true);
        if (!$product || !$product->delete()) {
            throw new \Exception('Product not found or maybe deleted', 404);
        }

        return $product->toApiArray();
    }
}
