<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 11:13 ص
 */

namespace Shop_products\Services\Cache;

use Shop_products\Enums\QueueNamesEnum;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Exceptions\NotFoundException;
use Shop_products\Interfaces\DataSourceInterface;
use Shop_products\Repositories\ProductRepository;
use Shop_products\RequestHandler\Queue\QueueRequestHandler;

class ProductCache implements DataSourceInterface
{

    const INDEX_NAME = 'product';

    /** @var \Redis $redisInstance */
    private static $redisInstance;

    /** @var self */
    private static $instance;

    private static $cacheKey = 'vendors:%s';

    /**
     * @return ProductCache
     * @throws \RedisException
     */
    public static function getInstance()
    {
        self::$redisInstance = \Phalcon\Di::getDefault()->getShared('productsCache');
        return self::$instance ?? self::$instance = new self;
    }

    public static function hDelete($key, ...$hashKeys)
    {
        return call_user_func_array([self::$redisInstance, 'hDel'], array_merge([$key], array_shift($hashKeys)));
    }

    /**
     * Get cache key
     *
     * @param string $vendorId
     * @param string $categoryId
     * @return string
     *
     * @throws \Exception
     */
    private function getKey(string $vendorId, ?string $categoryId = null)
    {
        if (!empty($vendorId) && empty($categoryId)) {
            return sprintf(self::$cacheKey, $vendorId.':*');
        } elseif (!empty($categoryId) && !empty($vendorId)) {
            return sprintf(self::$cacheKey, $vendorId.':categories:'.$categoryId);
        } else {
            throw new \Exception('You should provide all arguments to get cache key');
        }
    }

    /**
     * @param string $key
     * @param string $hashKey
     * @return mixed
     */
    private static function hGet(string $key, string $hashKey)
    {
        return json_decode(self::$redisInstance->hGet($key, $hashKey), true);
    }

    /**
     * Set product in cache
     *
     * @param string $vendorId
     * @param string $categoryId
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    public function setInCache(string $vendorId, string $categoryId, array $data)
    {
        return self::$redisInstance->hSet($this->getKey($vendorId, $categoryId), $data['productId'], json_encode($data));
    }

    /**
     * @param string $vendorId
     * @param array $products
     * @throws \Exception
     */
    public function setInCacheByVendorId(string $vendorId, array $products): void
    {
        foreach ($products as $product) {
            $this->setInCache($vendorId, $product['productCategoryId'], $product);
        }
    }

    /**
     * Invalidate product cache
     *
     * @param string $vendorId
     * @param string $categoryId
     * @param array|null $productsIds
     * @return int|mixed
     * @throws \Exception
     */
    public function invalidateCache(string $vendorId, string $categoryId, ?array $productsIds = null)
    {
        $cacheKey = $this->getKey($vendorId, $categoryId);
        if ($productsIds) {
            return forward_static_call('self::hDelete', $cacheKey, $productsIds);
        }
        return self::$redisInstance->del($cacheKey);
    }

    /**
     * Update product cache
     *
     * @param string $vendorId
     * @param string $categoryId
     * @param string $productId
     * @param array $data
     * @return bool|int
     *
     * @throws \Exception
     */
    public function updateCache(string $vendorId, string $categoryId, string $productId, array $data)
    {
        return self::$redisInstance->hSet($this->getKey($vendorId, $categoryId), $productId, json_encode($data));
    }

    /**
     * Get products by category id
     *
     * @param string $categoryId
     * @param string $vendorId
     * @return array
     * @throws \Exception
     */
    public function getByCategoryId(string $categoryId, string $vendorId): ?array
    {
        $cacheKey = $this->getKey($vendorId, $categoryId);
        $result = self::$redisInstance->hGetAll($cacheKey);
        if ($result) {
            $result = array_values(array_map(function ($product) {
                return json_decode($product, true);
            }, $result));
        }
        return $result;
    }

    /**
     * Get products by vendor id
     *
     * @param string $vendorId
     * @param bool $editMode
     * @param int $cursor
     * @return array
     * @throws \Exception
     */
    public function getByVendorId(string $vendorId, bool $editMode = false, int $cursor = 0): ?array
    {
        $result = [];
        $hKeys = self::$redisInstance->keys($this->getKey($vendorId));
        if ($hKeys) {
            foreach ($hKeys as $hKey) {
                foreach (self::$redisInstance->hGetAll($hKey) as $product) {
                    $result[] = json_decode($product, true);
                }
            }
        }

        return $result;
    }

    /**
     * Get product by id
     *
     * @param string $productId
     * @param string|null $vendorId
     * @param string|null $categoryId
     * @param bool $editMode
     * @param bool $getExtraInfo
     * @return array
     * @throws \Exception
     */
    public function getById(
        string $productId,
        string $vendorId,
        string $categoryId = null,
        bool $editMode = false,
        ?bool $getExtraInfo = true
    )
    {
        if (empty($vendorId) || empty($categoryId)) {
            throw new \Exception('Missing vendorId or categoryId');
        }
        if ($product = self::hGet($this->getKey($vendorId, $categoryId), $productId)) {
            return $product;
        }
        return [];
    }

    /**
     * @param array $product
     * @throws ArrayOfStringsException
     */
    public static function indexProduct(array $product): void
    {
        if (empty($product)) {
            return;
        }
        (new QueueRequestHandler(QueueRequestHandler::REQUEST_TYPE_ASYNC))
            ->setQueueName(QueueNamesEnum::PRODUCT_ASYNC_QUEUE)
            ->setService('indexing')
            ->setMethod('add')
            ->setData([
                'id' => $product['productId'],
                'title' => $product['productTitle'],
                'linkSLug' => $product['productLinkSlug']
            ])->sendAsync();
    }

    /**
     * @param string $productId
     * @throws ArrayOfStringsException
     */
    public static function deleteProductIndex(string $productId): void
    {
        if (empty($productId)) {
            return;
        }

        (new QueueRequestHandler(QueueNamesEnum::PRODUCT_ASYNC_QUEUE))
            ->setQueueName(QueueNamesEnum::PRODUCT_ASYNC_QUEUE)
            ->setService('indexing')
            ->setMethod('delete')
            ->setData([
                'id' => $productId
            ])->sendAsync();
    }
}