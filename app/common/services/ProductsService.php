<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:54 م
 */

namespace Shop_products\Services;


use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Repositories\ProductRepository;
use Shop_products\Services\Cache\ProductCache;

class ProductsService
{
    /**
     * @return ProductCache|ProductRepository
     * @throws \Exception
     */
    private function getDataSource()
    {
        try {
            return ProductCache::getInstance();
        } catch (\RedisException $exception) {
            return ProductRepository::getInstance();
        } catch (\Throwable $exception) {
            throw new \Exception('No data source available');
        }
    }

    private function getRepository()
    {
        return ProductRepository::getInstance();
    }

    /**
     * @param array $getBy
     * @return array
     * @throws \Exception
     */
    public function getAll(array $getBy)
    {
        if (!empty($getBy['vendorId']) && empty($getBy['categoryId'])) {
            return $this->getDataSource()->getByVendorId($getBy['vendorId']);
        } elseif (!empty($getBy['vendorId']) && !empty($getBy['vendorId'])) {
            return $this->getDataSource()->getByCategoryId($getBy['categoryId'], $getBy['vendorId']);
        } else {
            throw new \Exception('Unknown modifier');
        }
    }

    public function getProduct(string $vendorId, string $categoryId, string $productId)
    {
        if ($product = self::getDataSource()->getById($productId, $vendorId, $categoryId)) {
            return $product;
        }
    }

    /**
     * Create product
     *
     * @param array $data
     * @return array
     * @throws ArrayOfStringsException
     * @throws \RedisException
     * @throws \Exception
     */
    public function create(array $data)
    {
        $product = $this->getRepository()->create($data);
        ProductCache::getInstance()->setInCache($data['productVendorId'], $data['productCategoryId'], $data);
        return $product->toApiArray();
    }

    /**
     * Update product
     *
     * @param string $productId
     * @param array $data
     * @return array
     * @throws ArrayOfStringsException
     * @throws \RedisException
     * @throws \Exception
     */
    public function update(string $productId, array $data)
    {
        $product = self::getRepository()->update($productId, $data)->toApiArray();
        ProductCache::getInstance()->updateCache($product['productVendorId'], $product['productCategoryId'], $productId, $product);
        return $product;
    }

    /**
     * Delete product
     *
     * @param string $productId
     * @return bool
     * @throws \RedisException
     * @throws \Exception
     */
    public function delete(string $productId)
    {
        $deletedProduct = self::getRepository()->delete($productId);
        ProductCache::getInstance()->invalidateCache($deletedProduct['productVendorId'], $deletedProduct['productCategoryId'], [$productId]);
        return true;
    }
}