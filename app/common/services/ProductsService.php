<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:54 م
 */

namespace app\common\services;


use app\common\models\embedded\Properties;
use app\common\services\user\UserService;
use app\common\repositories\{ProductRepository,
    ImageRepository,
    QuestionRepository,
    RateRepository,
    VariationRepository};
use app\common\services\cache\ProductCache;
use app\common\exceptions\{OperationFailed, NotFound, OperationNotPermitted};
use app\common\enums\{
    AccessLevelsEnum, QueueNamesEnum
};
use Mechpave\ImgurClient\Entity\Album;
use app\common\requestHandler\queue\QueueRequestHandler;
use app\common\utils\ImgurUtil;

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
     * Check if category exists
     *
     * @param string $categoryId
     * @param string $vendorId
     *
     * @throws \ErrorException
     * @throws \Exception
     */
    public function checkCategoryExistence(string $categoryId, string $vendorId)
    {
        $exists = $this->getQueueRequestHandler()
            ->setQueueName(QueueNamesEnum::CATEGORY_SYNC_QUEUE)
            ->setService('category')
            ->setMethod('getCategories')
            ->setData([
                'ids' => [$categoryId]
            ])
            ->setServiceArgs([
                'storeId' => $vendorId
            ])
            ->sendSync();

        if (empty($exists) || false === $exists) {
            throw new NotFound('Category not found or maybe deleted');
        }
    }

    /**
     * @param array $params
     * @param int $accessLevel
     * @return array
     * @throws \Exception
     */
    public function getAll(array $params, int $accessLevel = AccessLevelsEnum::NORMAL_USER)
    {
        $page = $params['page'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $vendorId = array_key_exists('storeId', $params) ? $params['storeId'] : null;
        $categoryId = array_key_exists('categoryId', $params) ? $params['categoryId'] : null;

        $products = ProductRepository::getInstance()->getByIdentifier($vendorId, $categoryId, $limit, $page, $sort, false, true, true);
        $totalCount = ProductRepository::getInstance()->countAll($vendorId, $categoryId);

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
     * @throws OperationNotPermitted
     */
    public function getProduct(string $productId, bool $forOwner = false): array
    {
        $product = ProductRepository::getInstance()->getById($productId, false, true, true);
        if ($forOwner) {
            /** @var UserService $userService */
            $userService = \Phalcon\Di::getDefault()->getUserService();
            if ($product->productStoreId != $userService->storeId) {
                throw new OperationNotPermitted('You are not allowed to view this entity');
            }
        }
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
        $this->checkCategoryExistence($data['productCategoryId'], $data['productStoreId']);

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
     * @throws NotFound
     * @throws \Exception
     */
    public function delete(string $productId)
    {
        $deletedProduct = ProductRepository::getInstance()->delete($productId)->toApiArray();
        try {
            ProductCache::getInstance()->invalidateCache($deletedProduct['productStoreId'], $deletedProduct['productCategoryId'], [$productId]);
            (new QueueRequestHandler(QueueRequestHandler::REQUEST_TYPE_ASYNC))
                ->setQueueName(QueueNamesEnum::PRODUCT_ASYNC_QUEUE)
                ->setService('products')
                ->setMethod('deleteExtraInfo')
                ->setData([
                    'product_id' => $deletedProduct['productId']
                ])->sendAsync();
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
        Properties::findFirst([
                ['product_id' => $productId]
            ])->delete();

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
     * @throws NotFound
     * @throws OperationFailed
     */
    public function updateQuantity(string $entityId, array $data): array
    {
        $amount = $data['amount'];
        $operator = $data['operator'];
        return ProductRepository::getInstance()->updateQuantity($entityId, $amount, $operator)->toApiArray();
    }

    /**
     * @param string $productId
     * @param array $data
     * @return array
     * @throws OperationFailed
     * @throws NotFound
     */
    public function createVariation(string $productId, array $data): array
    {
        if (empty($data['userId']) || empty($data['quantity']) || empty($data['price'])) {
            throw new \InvalidArgumentException('variation has invalid input', 400);
        }

        $userId = $data['userId'];
        $quantity = $data['quantity'];
        $price = $data['price'];
        $imageId = array_key_exists('imageId', $data) ? $data['imageId'] : null;
        $attributes = array_key_exists('attributes', $data) ? $data['attributes'] : [];
        $salePrice = array_key_exists('salePrice', $data) ? $data['salePrice'] : 0;
        $sku = $data['sku'];

        if ($imageId) {
            // Throw NotFound exception if image does not exist
            ImageRepository::getInstance()->getUnused($imageId);
        }

        $variation = VariationRepository::getInstance()
            ->create($productId, $userId, $imageId, $quantity, $sku, $price, $salePrice, $attributes)
            ->toApiArray();

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
