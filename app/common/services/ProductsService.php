<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:54 م
 */

namespace app\common\services;


use app\common\repositories\ImageRepository;
use app\common\repositories\QuestionRepository;
use app\common\repositories\RateRepository;
use Mechpave\ImgurClient\Entity\Album;
use app\common\enums\AccessLevelsEnum;
use app\common\enums\QueueNamesEnum;
use app\common\exceptions\ArrayOfStringsException;
use app\common\exceptions\NotFoundException;
use app\common\models\Product;
use app\common\repositories\ProductRepository;
use app\common\requestHandler\queue\QueueRequestHandler;
use app\common\services\cache\ProductCache;
use app\common\utils\ImgurUtil;

class ProductsService
{
    const DEFAULT_LIMIT = 1;

    /** @var QueueRequestHandler */
    private $queueRequestHandler;

    /**
     * @param string $requestType
     * @return QueueRequestHandler
     */
    public function getQueueRequestHandler($requestType = QueueRequestHandler::REQUEST_TYPE_SYNC): QueueRequestHandler
    {
        return $this->queueRequestHandler ??
            $this->queueRequestHandler = new QueueRequestHandler($requestType);
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
            ->setMethod('getCategory')
            ->setData([
                'categoryId' => $categoryId
            ])
            ->setServiceArgs([
                'vendorId' => $vendorId
            ])
            ->sendSync();

        if (empty($exists)) {
            throw new NotFoundException('Category not found or maybe deleted');
        }
    }

    /**
     * @param string $productId
     * @return bool
     * @throws \Exception
     */
    public function checkProductExistence(string $productId): bool
    {
        return (bool) ProductRepository::getInstance()->isExists($productId);
    }

    /**
     * @param array $params
     * @param int $accessLevel
     * @return array
     * @throws \Exception
     */
    public function getAll(array $params, int $accessLevel = AccessLevelsEnum::NORMAL_USER)
    {
        $editMode = false;
        if ($accessLevel > 0) {
            $editMode = true;
        }

        $page = $params['page'];
        $limit = $params['limit'];

        if (empty($limit) && empty($page)) {
            throw new \Exception('you must provide page or cursor', 400);
        }

        $vendorId = array_key_exists('vendorId', $params) ? $params['vendorId'] : null;
        $categoryId = array_key_exists('categoryId', $params) ? $params['categoryId'] : null;

        // get by category id or vendor id or both
        try {
            if (!$editMode) {
                $products = ProductCache::getInstance()->getByIdentifier($categoryId, $vendorId);
            } else {
                $products = ProductRepository::getInstance()->getByIdentifier($vendorId, $categoryId, $limit, $page, $editMode, true, true);
            }
        } catch (\RedisException $exception) {
            $products = ProductRepository::getInstance()->getByIdentifier($vendorId, $categoryId, $limit, $page, $editMode, true, true);
        }
        return $products;
    }

    /**
     * Get product by id
     *
     * @param string $vendorId
     * @param string $categoryId
     * @param string $productId
     * @return array
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getProduct(
        string $vendorId,
        string $categoryId,
        string $productId
    ): array
    {
        try {
            $product = ProductCache::getInstance()->getById($productId, $vendorId, $categoryId);
            if (empty($product)) {
                $product = ProductRepository::getInstance()->getById($productId, $vendorId, false, true, true)->toApiArray();
                ProductCache::getInstance()->updateCache($vendorId, $categoryId, $productId, $product);
            }
        } catch (\RedisException $exception) {
            $product = ProductRepository::getInstance()->getById($productId, $vendorId, false, true, true);
        }
        return $product;
    }

    /**
     * Create product
     *
     * @param array $data
     * @return array
     *
     * @throws ArrayOfStringsException
     * @throws \Exception
     */
    public function create(array $data)
    {
        $this->checkCategoryExistence($data['productCategoryId'], $data['productVendorId']);
        if (!empty($album = $this->createAlbum($data['productId']))) {
            $data['productAlbumId'] = $album['albumId'];
            $data['productAlbumDeleteHash'] = $album['deleteHash'];
        }
        $product = ProductRepository::getInstance()->create($data);
        try {
            if ($product['isPublished']) {
                $productCacheData = $this->unsetSensitiveData($product);
                ProductCache::getInstance()->setInCache($productCacheData['productVendorId'], $productCacheData['productCategoryId'], $productCacheData);
                ProductCache::indexProduct($productCacheData);
            }
        } catch (\RedisException $exception) {
            // do nothing
        }

        return $product;
    }

    /**
     * Update product
     *
     * @param string $productId
     * @param array $data
     * @param string $vendorId
     * @return array
     *
     * @throws ArrayOfStringsException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function update(string $productId, array $data, string $vendorId)
    {
        $product = ProductRepository::getInstance()->update($productId, $vendorId, $data);
        try {
            if ($product['isPublished']) {
                unset($product['isPublished']);
                ProductCache::getInstance()->updateCache($product['productVendorId'], $product['productCategoryId'], $productId, $product);
                ProductCache::indexProduct($product);
            } else {
                ProductCache::getInstance()->invalidateCache($product['productVendorId'], $product['productCategoryId'], [$productId]);
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
     * @param string $vendorId
     * @return bool
     *
     * @throws ArrayOfStringsException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function delete(string $productId, string $vendorId)
    {
        $deletedProduct = ProductRepository::getInstance()->delete($productId, $vendorId);
        try {
            ProductCache::getInstance()->invalidateCache($deletedProduct['productVendorId'], $deletedProduct['productCategoryId'], [$productId]);
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
     * @throws ArrayOfStringsException
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function deleteExtraInfo(string $productId): void
    {
        /** Delete product related document */
        ProductRepository::getInstance()
            ->getCollection()::findFirst([
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
     * @param string $productId
     * @return array
     */
    public function createAlbum(string $productId): array
    {
        $data = [];
        /** @var Album $album */
        $album = (new ImgurUtil())->createAlbum($productId);
        if (!empty($album)) {
            $data = [
                'albumId' => $album->getAlbumId(),
                'deleteHash' => $album->getDeleteHash()
            ];
        }
        return $data;
    }

    /**
     * @param array $product
     * @return array
     */
    private function unsetSensitiveData(array $product)
    {
        unset(
            $product['productUserId'],
            $product['productAlbumDeleteHash'],
            $product['isPublished']
        );
        return $product;
    }
}