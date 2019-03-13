<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 11:13 ص
 */

namespace Shop_products\Services\Cache;

use Shop_products\Interfaces\DataSourceInterface;
use Shop_products\Repositories\ProductRepository;

class ProductCache implements DataSourceInterface
{
    /** @var \Redis $instance */
    private static $instance;

    private static $cacheKey = 'vendors:%s';

    /**
     * @return ProductCache
     * @throws \RedisException
     */
    public static function getInstance()
    {
        self::$instance = \Phalcon\Di::getDefault()->getShared('productsCache');
        return new self;
    }

    public static function hDelete($key, ...$hashKeys)
    {
        return call_user_func_array([self::$instance, 'hDel'], array_merge([$key], array_shift($hashKeys)));
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
        return json_decode(self::$instance->hGet($key, $hashKey), true);
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
        return self::$instance->hSet($this->getKey($vendorId, $categoryId), $data['productId'], json_encode($data));
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
        return self::$instance->del($cacheKey);
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
        return self::$instance->hSet($this->getKey($vendorId, $categoryId), $productId, json_encode($data));
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
        $result = self::$instance->hGetAll($cacheKey);
        if ($result) {
            $result = array_values(array_map(function ($product) {
                return json_decode($product, true);
            }, $result));
        } else {
            $result = ProductRepository::getInstance()->getByCategoryId($categoryId, $vendorId);
            foreach ($result as $product) {
                $this->setInCache($vendorId, $categoryId, $product);
            }
        }
        return $result;
    }

    /**
     * Get products by vendor id
     *
     * @param string $vendorId
     * @param int $cursor
     * @return array
     * @throws \Exception
     */
    public function getByVendorId(string $vendorId, int $cursor = 0): ?array
    {
        $result = [];
        $hKeys = self::$instance->keys($this->getKey($vendorId));
        if ($hKeys) {
            foreach ($hKeys as $hKey) {
                foreach (self::$instance->hGetAll($hKey) as $product) {
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
     * @return string
     * @throws \Exception
     */
    public function getById(string $productId, string $vendorId = null, string $categoryId = null)
    {
        if (empty($vendorId) || empty($categoryId)) {
            throw new \Exception('Missing vendorId or categoryId');
        }
        if (!$product = self::hGet($this->getKey($vendorId, $categoryId), $productId)) {
            $repository = ProductRepository::getInstance();
            if (count($product = $repository->getById($productId))) {
                $product = $product->toApiArray();
                $this->setInCache($product['productVendorId'], $product['productCategoryId'], $product);
            }
        }
        return $product;
    }
}