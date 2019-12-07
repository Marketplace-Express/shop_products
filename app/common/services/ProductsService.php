<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 08:54 م
 */

namespace app\common\services;


use app\common\repositories\{
    ProductRepository, ImageRepository, QuestionRepository, RateRepository
};
use app\common\services\cache\{
    ProductCache, ImagesCache, QuestionsCache
};
use app\common\exceptions\{
    OperationFailed, NotFound
};
use app\common\enums\{
    AccessLevelsEnum, QueueNamesEnum
};
use Mechpave\ImgurClient\Entity\Album;
use app\common\requestHandler\queue\QueueRequestHandler;
use app\common\utils\ImgurUtil;

class ProductsService
{
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
            ->setMethod('getCategories')
            ->setData([
                'ids' => [$categoryId]
            ])
            ->setServiceArgs([
                'vendorId' => $vendorId
            ])
            ->sendSync();

        if (empty($exists)) {
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
        $editMode = false;
        if ($accessLevel > 0) {
            $editMode = true;
        }

        $page = $params['page'];
        $limit = $params['limit'];
        $sort = $params['sort'];

        $vendorId = array_key_exists('vendorId', $params) ? $params['vendorId'] : null;
        $categoryId = array_key_exists('categoryId', $params) ? $params['categoryId'] : null;

        // get by category id or vendor id or both
        if ($editMode) {
            $products = ProductRepository::getInstance()->getByIdentifier($vendorId, $categoryId, $limit, $page, $sort, true, false, false);
        } else {
            $products = ProductRepository::getInstance()->getByIdentifier($vendorId, $categoryId, $limit, $page, $sort, false, true, true);
        }

        $result = [];
        foreach ($products as $product) {
            $result[] = $product->toApiArray();
        }

        return $result;
    }

    /**
     * Get product by id
     *
     * @param string|null $productId
     * @return array
     *
     * @throws NotFound
     * @throws \Exception
     */
    public function getProduct(string $productId): array
    {
        $product = ProductRepository::getInstance()->getById($productId, false, true, true)->toApiArray();
//        $this->unsetSensitiveData($product);
        return $product;
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
        $this->checkCategoryExistence($data['productCategoryId'], $data['productVendorId']);
        if (!empty($album = $this->createAlbum($data['productId']))) {
            $data['productAlbumId'] = $album['albumId'];
            $data['productAlbumDeleteHash'] = $album['deleteHash'];
        }
        $product = ProductRepository::getInstance()->create($data);
        try {
            if ($product['isPublished']) {
                ProductCache::getInstance()->setInCache($product['productVendorId'], $product['productCategoryId'], $product);
                ProductCache::indexProduct($product);
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
                ProductCache::getInstance()->updateCache($product['productVendorId'], $product['productCategoryId'], $product);
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
     * @throws OperationFailed
     * @throws NotFound
     * @throws \Exception
     */
    public function delete(string $productId)
    {
        $deletedProduct = ProductRepository::getInstance()->delete($productId)->toApiArray();
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
     * @throws OperationFailed
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function deleteExtraInfo(string $productId): void
    {
        /** Delete product related document */
        ProductRepository::getInstance()
            ->getPropertiesCollection()::findFirst([
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
//        return $product;
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
}
