<?php
/**
 * User: Wajdi Jurry
 * Date: 27/02/19
 * Time: 12:11 م
 */

namespace app\common\services;


use Ehann\RediSearch\Index;
use Ehann\RediSearch\Suggestion;
use Phalcon\Di\Injectable;
use app\common\redis\DocumentMapper;
use app\common\enums\ProductsCacheIndexesEnum;

class IndexingService extends Injectable
{

    const INDEX_NAME_PREFIX = 'idx:';

    private $indexName = self::INDEX_NAME_PREFIX.ProductsCacheIndexesEnum::PRODUCT_INDEX_NAME;

    /** @var Index */
    private $redisIndexing;

    /** @var Suggestion */
    private $redisSuggesting;

    /** @var \Redis */
    private $redis;

    public function __construct()
    {
        $this->redis = $this->getDI()->get('productsCache');
        $this->redisIndexing = $this->getDI()->get('productsCacheIndex');
        $this->redisSuggesting = $this->getDI()->get('productsCacheSuggestion');
    }

    /**
     * Create an index
     *
     */
    private function create()
    {
        $this->redisIndexing
            ->addTextField('title', 1.0, true)
            ->addTextField('linkSlug', 1.0, false, false)
            ->create();
    }

    /**
     * Add document to index
     *
     * @param string $docId
     * @param string $title
     * @param string $linkSlug
     *
     * @throws \Ehann\RediSearch\Exceptions\FieldNotInSchemaException
     * @throws \Exception
     */
    public function add(string $docId, string $title, ?string $linkSlug)
    {
        if (empty($docId) || empty($title)) {
            throw new \Exception('Missing arguments');
        }
        if (!$this->redis->exists($this->indexName))  {
            $this->create();
        }
        $document = new DocumentMapper($docId);
        $document = $document->makeDocument($title, $linkSlug);
        $this->redisIndexing->add($document);
        $this->redisSuggesting->add($title, 1.0);
    }

    /**
     * Delete document
     *
     * @param string $id
     *
     * @throws \Exception
     */
    public function delete(string $id)
    {
        if (empty($id)) {
            throw new \Exception('Missing argument');
        }
        $this->redisIndexing->delete($id);
    }
}