<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:54 م
 */

namespace app\common\services;


use app\common\models\embedded\Properties;
use app\common\models\embedded\Variation;
use app\common\repositories\{
    ProductRepository,
    ImageRepository,
    QuestionRepository,
    RateRepository,
    VariationRepository
};
use app\common\services\cache\ProductCache;
use app\common\exceptions\{OperationFailed, NotFound};
use app\common\enums\QueueNamesEnum;
use app\common\requestHandler\queue\QueueRequestHandler;

class ProductsService
{
    /**
     * @param string $requestType
     * @return QueueRequestHandler
     */
    public function getQueueRequestHandler($requestType = QueueRequestHandler::REQUEST_TYPE_SYNC): QueueRequestHandler
    {
        return new QueueRequestHandler($requestType);
    }

    /**
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getAll(array $params)
    {
        $page = $params['page'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $storeId = array_key_exists('storeId', $params) ? $params['storeId'] : null;
        $categoryId = array_key_exists('categoryId', $params) ? $params['categoryId'] : null;

        $products = ProductRepository::getInstance()->getByIdentifier($storeId, $categoryId, $limit, $page, $sort, $params['editMode'], true, true);
        $totalCount = ProductRepository::getInstance()->countAll($storeId, $categoryId);

        $result = [];
        foreach ($products as $product) {
            $result[] = $product->toApiArray();
        }

        return [
            'products' => $result,
            'total' => $totalCount
        ];
    }

    /**
     * Get product by id
     *
     * @param string|null $productId
     * @param bool $forOwner
     * @return array
     *
     * @throws NotFound
     */
    public function getProduct(string $productId, bool $forOwner = false): array
    {
        $product = ProductRepository::getInstance()->getById($productId, $forOwner, true, true);
        return $product->toApiArray();
    }

    /**
     * Create product
     *
     * @param array $data
     * @return array
     *
     * @throws OperationFailed
     * @throws \Exception
     */
    public function create(array $data)
    {
        // Create product
        $product = ProductRepository::getInstance()->create($data);

        $productAsArray = $product->toApiArray();
        try {
            if ($product['isPublished']) {
                ProductCache::getInstance()->setInCache($product['productStoreId'], $product['productCategoryId'], $productAsArray);
                ProductCache::indexProduct($productAsArray);
            }
        } catch (\RedisException $exception) {
            // do nothing
        }

        return $productAsArray;
    }

    /**
     * Update product
     *
     * @param string $productId
     * @param array $data
     * @return array
     *
     * @throws OperationFailed
     * @throws NotFound
     * @throws \Exception
     */
    public function update(string $productId, array $data)
    {
        $product = ProductRepository::getInstance()->update($productId, $data)->toApiArray();
        try {
            if ($product['isPublished']) {
                unset($product['isPublished']);
                ProductCache::getInstance()->updateCache($product['productStoreId'], $product['productCategoryId'], $product);
                ProductCache::indexProduct($product);
            } else {
                ProductCache::getInstance()->invalidateCache($product['productStoreId'], $product['productCategoryId'], [$productId]);
            }
        } catch (\RedisException $exception) {
            // do nothing
        }
        return $product;
    }

    /**
     * Delete product
     *
     * @param string $productId
     * @return bool
     *
     * @throws OperationFailed
     */
    public function delete(string $productId)
    {
        $deletedProduct = ProductRepository::getInstance()->delete($productId)->toApiArray();
        try {
            ProductCache::getInstance()->invalidateCache($deletedProduct['productStoreId'], $deletedProduct['productCategoryId'], [$productId]);
            $this->getQueueRequestHandler(QueueRequestHandler::REQUEST_TYPE_ASYNC)
                ->setQueueName(QueueNamesEnum::PRODUCT_ASYNC_QUEUE)
                ->setRoute(sprintf('products/extra/%s', $productId))
                ->setMethod('delete')
                ->sendAsync();
        } catch (\RedisException $exception) {
            // do nothing
        }
        return true;
    }

    /**
     * @param string $productId
     * @throws \Exception
     * @throws OperationFailed
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function deleteExtraInfo(string $productId): void
    {
        /** Delete product related document */
        $properties = Properties::findFirst([
            ['product_id' => $productId]
        ]);

        if ($properties) {
            $properties->delete();
        }

        /** Delete product cache index */
        ProductCache::deleteProductIndex($productId);

        /** Delete product images, rates and questions */
        ImageRepository::getInstance()->deleteProductImages($productId);

        /** Delete product questions */
        QuestionRepository::getInstance()->deleteProductQuestions($productId);

        /** Delete product rates */
        RateRepository::getInstance()->deleteProductRates($productId);
    }

    /**
     * Update entity quantity. It could be product or variation
     *
     * @param string $entityId
     * @param array $data
     * @return array
     */
    public function updateQuantity(string $entityId, array $data): array
    {
        $amount = $data['amount'];
        $operator = $data['operator'];
        return ProductRepository::getInstance()->updateQuantity($entityId, $amount, $operator)->toApiArray();
    }

    /**
     * @param array $data
     * @return Variation
     * @throws OperationFailed
     * @throws NotFound
     */
    public function createVariation(array $data): Variation
    {
        $userId = $data['userId'];
        $productId = $data['productId'];
        $quantity = $data['quantity'];
        $price = $data['price'];
        $imageId = array_key_exists('imageId', $data) ? $data['imageId'] : null;
        $attributes = array_key_exists('attributes', $data) ? $data['attributes'] : [];
        $salePrice = array_key_exists('salePrice', $data) ? $data['salePrice'] : 0;
        $sku = $data['sku'];

        $variation = VariationRepository::getInstance()
            ->create($productId, $userId, $imageId, $quantity, $sku, $price, $salePrice, $attributes);

        // Mark image as used
        ImageRepository::getInstance()->markAsUsed([$imageId]);

        return $variation;
    }

    /**
     * @param string $variationId
     * @param array $data
     * @return Variation
     */
    public function updateVariation(string $variationId, array $data = []): Variation
    {
        $quantity = $data['quantity'];
        $price = $data['price'];
        $imageId = array_key_exists('imageId', $data) ? $data['imageId'] : null;
        $attributes = array_key_exists('attributes', $data) ? $data['attributes'] : [];
        $salePrice = array_key_exists('salePrice', $data) ? $data['salePrice'] : 0;
        $sku = $data['sku'];

        $variation = VariationRepository::getInstance()
            ->update($variationId, $imageId, $quantity, $sku, $price, $salePrice, $attributes);

        // Mark image as used
        ImageRepository::getInstance()->markAsUsed([$imageId]);

        return $variation;
    }

    /**
     * @param string $variationId
     * @return bool
     * @throws OperationFailed
     * @throws NotFound
     */
    public function deleteVariation(string $variationId)
    {
        return VariationRepository::getInstance()->deleteVariation($variationId);
    }
}
