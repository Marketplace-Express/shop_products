<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:54 م
 */

namespace Shop_products\Services;


use Mechpave\ImgurClient\Entity\Album;
use Phalcon\Mvc\ModelInterface;
use Shop_products\Enums\AccessLevelsEnum;
use Shop_products\Enums\QueueNamesEnum;
use Shop_products\Exceptions\ArrayOfStringsException;
use Shop_products\Exceptions\NotFoundException;
use Shop_products\Models\Product;
use Shop_products\Repositories\ProductRepository;
use Shop_products\RequestHandler\Queue\QueueRequestHandler;
use Shop_products\Services\Cache\ProductCache;
use Shop_products\Utils\ImgurUtil;
use RedisException;
use Exception;
use Throwable;

class ProductsService
{
    /** @var QueueRequestHandler */
    private $queueRequestHandler;

    /**
     * @return ProductCache|ProductRepository
     * @throws Exception
     */
    private function getDataSource()
    {
        try {
            return ProductCache::getInstance();
        } catch (RedisException $exception) {
            return $this->getRepository();
        } catch (Throwable $exception) {
            throw new Exception('No data source available');
        }
    }

    /**
     * @return ProductRepository
     */
    private function getRepository(): ProductRepository
    {
        return ProductRepository::getInstance();
    }

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
     * @throws Exception
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
            throw new Exception('Category not found or maybe deleted', 404);
        }
    }

    /**
     * @param array $identifier
     * @return array
     * @throws Exception
     */
    public function getAll(array $identifier, int $accessLevel = AccessLevelsEnum::NORMAL_USER)
    {
        if ($accessLevel !== 0) {
            $editMode = true;
        } else {
            $editMode = false;
        }
        if (!empty($identifier['vendorId']) && empty($identifier['categoryId'])) {
            return $this->getDataSource()->getByVendorId($identifier['vendorId']);
        } elseif (!empty($identifier['vendorId']) && !empty($identifier['categoryId'])) {
            return $this->getDataSource()->getByCategoryId($identifier['categoryId'], $identifier['vendorId']);
        } else {
            throw new Exception('Unknown modifier');
        }
    }

    /**
     * Get product by id
     *
     * @param string $vendorId
     * @param string $categoryId
     * @param string $productId
     * @param int $accessLevel
     * @return ModelInterface|Product
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function getProduct(
        string $vendorId,
        string $categoryId,
        string $productId,
        int $accessLevel = AccessLevelsEnum::NORMAL_USER
    )
    {
        if ($accessLevel !== 0) {
            $editMode = true;
        } else {
            $editMode = false;
        }
        return self::getDataSource()->getById($productId, $vendorId, $categoryId, $editMode);
    }

    /**
     * Create product
     *
     * @param array $data
     * @return array
     * @throws ArrayOfStringsException
     * @throws Exception
     */
    public function create(array $data)
    {
        $this->checkCategoryExistence($data['productCategoryId'], $data['productVendorId']);
        $product = $this->getRepository()->create($data);
        if (!empty($album = $this->createAlbum($data['productId']))) {
            $data['productAlbumId'] = $album['albumId'];
            $data['productAlbumDeleteHash'] = $album['deleteHash'];
        }
        try {
            if ($product->isPublished) {
                ProductCache::getInstance()->setInCache($data['productVendorId'], $data['productCategoryId'], $data);
                ProductCache::indexProduct($product->toApiArray());
            }
        } catch (RedisException $exception) {
            // do nothing
        }

        return $product->toApiArray();
    }

    /**
     * Update product
     *
     * @param string $productId
     * @param array $data
     * @param string $vendorId
     * @return array
     * @throws ArrayOfStringsException
     * @throws NotFoundException
     * @throws Exception
     */
    public function update(string $productId, array $data, string $vendorId)
    {
        $product = self::getRepository()->update($productId, $vendorId, $data)->toApiArray();
        try {
            if ($product['isPublished']) {
                ProductCache::getInstance()->updateCache($product['productVendorId'], $product['productCategoryId'], $productId, $product);
            } else {
                ProductCache::getInstance()->invalidateCache($product['productVendorId'], $product['productCategoryId'], [$productId]);
            }
        } catch (RedisException $exception) {
            // do nothing
        }
        return $product;
    }

    /**
     * Delete product
     *
     * @param string $productId
     * @return bool
     * @throws Exception
     */
    public function delete(string $productId)
    {
        $deletedProduct = self::getRepository()->delete($productId);
        try {
            ProductCache::getInstance()->invalidateCache($deletedProduct['productVendorId'], $deletedProduct['productCategoryId'], [$productId]);
        } catch (RedisException $exception) {
            // do nothing
        }
        return true;
    }

    /**
     * @param string $productId
     * @throws Exception
     * @throws ArrayOfStringsException
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
}