<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:58 م
 */

namespace app\common\repositories;


use app\common\enums\{
    MongoQueryOperatorsEnum
};
use app\common\exceptions\{
    OperationFailed,
    NotFound
};
use app\common\models\{
    factory\PropertiesFactory,
    embedded\Properties,
    sorting\SortProduct,
    Product
};
use app\common\interfaces\DataSourceInterface;
use app\common\enums\QuantityOperatorsEnum;
use Phalcon\Mvc\Collection\Exception;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\Model\Transaction\Exception as TxException;

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
     * @param string $productId
     * @param array $columns
     * @return array
     * @throws NotFound
     * @throws \InvalidArgumentException
     */
    public function getColumnsForProduct(string $productId, array $columns)
    {
        if (empty($columns)) {
            throw new \InvalidArgumentException('please provide columns to select', 400);
        }

        if (array_diff($columns, $this->getModel()->columnMap())) {
            throw new \InvalidArgumentException('invalid provided columns', 400);
        }

        /** @var array $product */
        $product = Product::findFirst([
            'columns' => $columns,
            'conditions' => 'productId = :productId:',
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
     * @param bool $getProperties
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
        bool $getProperties = false
    ): array
    {
        $binds = [];

        $conditions = 'productVendorId = :productVendorId:';

        if ($categoryId) {
            $conditions .= ' AND productCategoryId = :productCategoryId:';
            $binds['productCategoryId'] = $categoryId;
        }

        if (!$editMode) {
            $conditions .= ' AND isPublished = TRUE';
        }

        $products = Product::find([
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

        return $result;
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
        /** @var Product $productModel */
        $productModel = Product::model(true, false, true);

        if (!$productModel->create($data, $productModel::getWhiteList())) {
            throw new OperationFailed($productModel->getMessages(), 400);
        }

        $data['productId'] = $productModel->productId;
        $properties = PropertiesFactory::create($data['productType'], $data);
        if (count(array_intersect(array_keys($data), $properties->attributes()))) {
            if (!$properties->save()) {
                throw new OperationFailed($properties->getMessages(), 400);
            }
            $productModel->properties = $properties;
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
     * @throws Exception
     */
    public function update(string $productId, array $data): Product
    {
        $product = $this->getById($productId, true, true, false);

        // Start a transaction
        $txManager = new TxManager($product->getDI());

        try {

            $product->setTransaction($txManager->getOrCreateTransaction());
            if (!$product->update($data, Product::getWhiteList())) {
                throw new OperationFailed($product->getMessages(), 400);
            }

            $data['productId'] = $productId;
            $properties = PropertiesFactory::create($product->productType, $data);
            if (count(array_intersect(array_keys($data), $properties->attributes()))) {
                if ($product->properties) {
                    $properties = $product->properties;
                    $properties->setAttributes($data);
                }

                if (!$properties->save()) {
                    $txManager->rollback();
                    throw new OperationFailed($product->properties->getMessages(), 400);
                }

                $product->properties = $properties;
            }

            if (!$product->save()) {
                $txManager->rollback();
                throw new OperationFailed($product->properties->getMessages(), 400);
            }

            $txManager->commit();
        } catch (TxException $exception) {
            $txManager->rollback();
            throw new OperationFailed('Cannot update product');
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

    /**
     * @param string $productId
     * @param array $album
     * @return bool
     * @throws NotFound
     * @throws OperationFailed
     */
    public function setAlbum(string $productId, array $album)
    {
        $product = Product::findFirst([
            'conditions' => 'productId = :productId:',
            'bind' => ['productId' => $productId]
        ]);

        if (!$product) {
            throw new NotFound('Product not found or maybe deleted');
        }

        $product->productAlbumId = $album['albumId'];
        $product->productAlbumDeleteHash = $album['deleteHash'];

        if (!$product->save()) {
            throw new OperationFailed('Cannot create an album for product');
        }

        return true;
    }

    public function countAll(string $vendorId, ?string $categoryId = null)
    {
        $conditions = 'productVendorId = :vendorId:';
        $bind = ['vendorId' => $vendorId];

        if (!empty($categoryId)) {
            $conditions .= ' AND productCategoryId = :categoryId:';
            $bind['categoryId'] = $categoryId;
        }

        return Product::count([
            'conditions' => $conditions,
            'bind' => $bind
        ]);
    }
}
