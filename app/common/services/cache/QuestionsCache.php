<?php
/**
 * User: Wajdi Jurry
 * Date: ٣١‏/٨‏/٢٠١٩
 * Time: ١:٥٨ ص
 */

namespace app\common\services\cache;


class QuestionsCache
{
    /**
     * @var \Redis
     */
    private static $cacheInstance;

    /**
     * @var QuestionsCache
     */
    private static $instance;

    private static $cacheKey = 'product:%s';

    static private function establishConnection()
    {
        self::$cacheInstance = \Phalcon\Di::getDefault()->getQuestionsCache();
    }

    /**
     * @return QuestionsCache
     */
    static public function getInstance()
    {
        self::establishConnection();
        return self::$instance ?? self::$instance = new self;
    }

    /**
     * @param string $productId
     * @param array $question
     * @return bool|int
     */
    public function set(string $productId, array $question)
    {
        return self::$cacheInstance->hSet(sprintf(self::$cacheKey, $productId), $question['id'], json_encode($question));
    }

    /**
     * @param string $productId
     * @return array
     */
    public function getAll(string $productId)
    {
        return array_values(array_map(function ($question) {
            return json_decode($question, true);
        }, self::$cacheInstance->hGetAll(sprintf(self::$cacheKey, $productId))
        ));
    }

    /**
     * @param array $question
     */
    public function updateCache(array $question)
    {
        $cacheKey = sprintf(self::$cacheKey, $question['productId']);
        if (self::$cacheInstance->hExists($cacheKey, $question['id'])) {
            self::$cacheInstance->hSet($cacheKey, $question['id'], json_encode($question));
        }
    }

    /**
     * @param array $question
     * @return bool|int
     */
    public function invalidate(array $question)
    {
        return self::$cacheInstance->hDel(sprintf(self::$cacheKey, $question['productId']), $question['id']);
    }
}
