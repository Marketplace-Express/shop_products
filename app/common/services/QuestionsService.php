<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٧‏/٧‏/٢٠١٩
 * Time: ٨:١٢ م
 */

namespace app\common\services;


use app\common\repositories\ProductRepository;
use app\common\repositories\QuestionRepository;
use app\common\services\cache\ProductCache;
use app\common\services\cache\QuestionsCache;
use app\common\services\user\UserService;

class QuestionsService
{
    /** @var QuestionRepository */
    private $repository;

    /**
     * @return UserService
     */
    protected function getUserService(): UserService
    {
        return \Phalcon\Di::getDefault()->getUserService();
    }

    /**
     * @return QuestionRepository
     */
    protected function getRepository(): QuestionRepository
    {
        return $this->repository ?? $this->repository = new QuestionRepository();
    }

    /**
     * @param string $productId
     * @param string $text
     * @return array
     * @throws \RedisException
     * @throws \app\common\exceptions\NotFound
     * @throws \app\common\exceptions\OperationFailed
     */
    public function create(string $productId, string $text)
    {
        $userId = $this->getUserService()->userId;
        $question = $this->getRepository()->create($userId, $productId, $text);
        QuestionsCache::getInstance()->set($productId, $question);
        return $question;
    }

    /**
     * @param string $id
     * @param string $text
     * @return array
     * @throws \RedisException
     * @throws \app\common\exceptions\NotFound
     * @throws \app\common\exceptions\OperationFailed
     */
    public function update(string $id, string $text)
    {
        $question = $this->getRepository()->update($id, $text);
        QuestionsCache::getInstance()->updateCache($question);
        return $question;
    }

    /**
     * @param string $id
     * @return bool
     * @throws \RedisException
     * @throws \app\common\exceptions\NotFound
     * @throws \app\common\exceptions\OperationFailed
     */
    public function delete(string $id)
    {
        $question = $this->getRepository()->delete($id);
        QuestionsCache::getInstance()->invalidate($question);
        return true;
    }

    /**
     * @param string $id
     * @return array
     * @throws \app\common\exceptions\NotFound
     */
    public function getById(string $id)
    {
        return $this->getRepository()->getById($id);
    }

    /**
     * @param string $productId
     * @return array
     */
    public function getAll(string $productId)
    {
        return $this->getRepository()->getAll($productId);
    }
}
