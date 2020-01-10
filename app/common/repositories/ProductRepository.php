<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:58 م
 */

namespace app\common\repositories;

use app\common\enums\QuantityOperatorsEnum;
use app\common\models\sorting\SortProduct;
use Phalcon\Mvc\Collection\Exception;
use Phalcon\Mvc\Model\Resultset;
use app\common\enums\MongoQueryOperatorsEnum;
use app\common\enums\ProductTypesEnum;
use app\common\exceptions\OperationFailed;
use app\common\exceptions\NotFound;
use app\common\interfaces\DataSourceInterface;
use app\common\models\embedded\Properties;
use app\common\models\DownloadableProperties;
use app\common\models\PhysicalProperties;
use app\common\models\Product;

class ProductRepository extends BaseRepository implements DataSourceInterface
{
    /**
     * @param bool $new
     * @param bool $attachRelations
     * @param bool $editMode
     * @return Product
     */
    public function getModel(bool $new = false, bool $attachRelations = false, bool $editMode = false)
    {
         return Product::model($new, $attachRelations, $editMode);
    }

    /**
     * @param bool $new
     * @return Properties
     */
    public function getPropertiesCollection(bool $new = false): Properties
    {
        return Properties::model($new);
    }

    /**
     * @param string $productId
     * @param array $columns
     * @param bool $editMode
     * @return array
     * @throws NotFound
     * @throws \InvalidArgumentException
     */
    public function getColumnsForProduct(string $productId, array $columns, bool $editMode = false)
    {
        if (empty($columns)) {
            throw new \InvalidArgumentException('please provide columns to select', 400);
        }

        if (array_diff($columns, $this->getModel()->columnMap())) {
            throw new \InvalidArgumentException('invalid provided columns', 400);
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
     * @param bool $editMode
     * @param bool $getProperties
     * @param bool $attachRelations
     * @return Product
     *
     * @throws NotFound
     * @throws \Exception
     */
    public function getById(
        string $productId,
        bool $editMode = false,
        bool $getProperties = true,
        bool $attachRelations = false
    )
    {
        $conditions = 'productId = :productId:';
        if ($editMode) {
            $conditions .= ' AND isPublished IN (true, false)';
        } else {
            $conditions .= ' AND isPublished = true';
        }

        $product = $this->getModel(false, $attachRelations, $editMode)::findFirst([
            'conditions' => $conditions,
            'bind' => [
                'productId' => $productId
            ]
        ]);

        if (!$product) {
            throw new NotFound('product not found or maybe deleted');
        }

        if ($getProperties) {
            $productProperties = $this->getPropertiesCollection()::findFirst([
                ['product_id' => $productId]
            ]);
            if (!empty($productProperties)) {
                $product->assign(['properties' => $productProperties]);
            }
        }

        return $product;
    }

    /**
     * Get products by category id
     *
     * @param string $vendorId
     * @param string $categoryId
     * @param null|int $limit
     * @param null|int $page
     * @param SortProduct $sort
     * @param bool $editMode
     * @param bool $attachRelations
     * @param bool $getExtraInfo
     * @return Product[]
     *
     * @throws \Exception
     */
    public function getByIdentifier(
        string $vendorId,
        ?string $categoryId,
        int $limit,
        int $page,
        SortProduct $sort,
        bool $editMode = false,
        bool $attachRelations = true,
        bool $getExtraInfo = false
    ): array
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

        $products = $this->getModel(
            false, $attachRelations, $editMode
        )::find([
            'conditions' => $conditions,
            'bind' => array_merge([
                'productVendorId' => $vendorId
            ], $binds),
            'limit' => $limit,
            'offset' => ($page - 1) * $limit,
            'order' => $sort->getSqlSort()
        ]);

        $result = [];
        foreach ($products as $product) {
            $result[] = $product;
        }

        if ($result && $getExtraInfo) {
            $productsIds = array_column($products->toArray(), 'productId');
            $productsExtraInfo = $this->getPropertiesCollection()::find([
                'conditions' => [
                    'product_id' => [MongoQueryOperatorsEnum::OP_IN => $productsIds]
                ]
            ]);
            if (!empty($productsExtraInfo)) {
                foreach ($result as $product) {
                    /** @var Product $product */
                    foreach ($productsExtraInfo as $extraInfo) {
                        if ($product->productId == $extraInfo->product_id) {
                            $product->assign(['extraInfo' => $extraInfo]);
                        }
                    }
                }
            }
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
     * @return Product
     *
     * @throws Exception
     * @throws OperationFailed
     * @throws \Exception
     */
    public function create(array $data): Product
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
            $propertiesCollection = $this->getPropertiesCollection(true);
            $propertiesCollection->setAttributes($productCollectionData);
            if (!$propertiesCollection->save()) {
                throw new OperationFailed($propertiesCollection->getMessages(), 400);
            }
            unset($productCollectionData['product_id']);
            $productModel->assign($propertiesCollection->toApiArray(), null, Product::getWhiteList());
        }
        return $productModel;
    }

    /**
     * Update product
     *
     * @param string $productId
     * @param array $data
     * @return Product
     * @throws NotFound
     * @throws OperationFailed
     */
    public function update(string $productId, array $data): Product
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

        $product = $this->getById($productId, true, false, false);

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
        return $product;
    }

    /**
     * Delete product
     *
     * @param string $productId
     * @return Product
     * @throws NotFound
     * @throws OperationFailed
     */
    public function delete(string $productId): Product
    {
        $product = $this->getById($productId, true, false, false);
        if (!$product) {
            throw new NotFound('Product not found or maybe deleted');
        }
        if (!$product->delete()) {
            throw new OperationFailed($product->getMessages());
        }

        return $product;
    }

    /**
     * @param string $productId
     * @param int $amount
     * @param string $operator
     * @return Product
     * @throws NotFound
     * @throws OperationFailed
     */
    public function updateQuantity(string $productId, int $amount, string $operator = QuantityOperatorsEnum::OPERATOR_INCREMENT): Product
    {
        $product = $this->getById($productId);
        return $product->updateQuantity($amount, $operator);
    }
}
