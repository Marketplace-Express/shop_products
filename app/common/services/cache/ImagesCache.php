<?php
/**
 * User: Wajdi Jurry
 * Date: ٣٠‏/٨‏/٢٠١٩
 * Time: ٢:٥٧ م
 */

namespace app\common\services\cache;


class ImagesCache
{
    /**
     * @var \Redis
     */
    private static $cacheInstance;

    /**
     * @var ImagesCache
     */
    private static $instance;

    /**
     * @var string
     */
    private static $cacheKey = 'product:%s';

    static private function establishConnection()
    {
        self::$cacheInstance = \Phalcon\Di::getDefault()->getImagesCache();
    }

    /**
     * @return ImagesCache
     */
    static public function getInstance()
    {
        self::establishConnection();
        return self::$instance ?? self::$instance = new self;
    }

    /**
     * @param string $productId
     * @param array $image
     * @return bool|int
     */
    public function set(string $productId, array $image)
    {
        return self::$cacheInstance->hSet(sprintf(self::$cacheKey, $productId), $image['imageId'], json_encode($image));
    }

    /**
     * @param string $productId
     * @return array
     */
    public function getAll(string $productId)
    {
        return array_values(array_map(function ($image) {
                return json_decode($image, true);
            }, self::$cacheInstance->hGetAll(sprintf(self::$cacheKey, $productId))
        ));
    }

    /**
     * @param string $productId
     * @param string $imageId
     * @return bool|int
     */
    public function invalidate(string $productId, string $imageId)
    {
        return self::$cacheInstance->hDel(sprintf(self::$cacheKey, $productId), $imageId);
    }

    /**
     * @param string $productId
     * @return int
     */
    public function invalidateProductImages(string $productId)
    {
        return self::$cacheInstance->delete(sprintf(self::$cacheKey, $productId));
    }

    /**
     * @param string $productId
     * @param array $images
     * @return bool
     */
    public function bulkProductSet(string $productId, array $images)
    {
        $success = true;
        foreach ($images as $image) {
            $success = self::$cacheInstance->hSet(sprintf(self::$cacheKey, $productId), $image['imageId'], json_encode($image));
        }
        return $success;
    }
}
