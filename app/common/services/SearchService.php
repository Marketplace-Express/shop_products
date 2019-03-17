<?php
/**
 * User: Wajdi Jurry
 * Date: 17/03/19
 * Time: 11:20 م
 */

namespace Shop_products\Services;


use Ehann\RediSearch\Index;
use Ehann\RediSearch\Suggestion;

class SearchService
{
    /**
     * @param string $instance
     * @return Index|Suggestion
     * @throws \Exception
     */
    public function getDataSource($instance = 'indexing')
    {
        if ($instance == 'indexing') {
            return \Phalcon\Di::getDefault()->get('productsCacheIndex');
        } elseif ($instance == 'suggestion') {
            return \Phalcon\Di::getDefault()->get('productsCacheSuggestion');
        } else {
            throw new \Exception('No data source available');
        }
    }

    /**
     * Autocomplete search
     *
     * @param array $searchParams
     * @return array
     *
     * @throws \Exception
     */
    public function autocomplete(array $searchParams = []): array
    {
        return ['results' => self::getDataSource()->get($searchParams['keyword'])];
    }

    /**
     * Categories search
     *
     * @param array $searchParams
     * @return array
     *
     * @throws \Ehann\RedisRaw\Exceptions\RedisRawCommandException
     * @throws \Exception
     */
    public function search(array $searchParams = []): array
    {
        return [
            'results' => self::getDataSource()->search($searchParams['keyword'])->getDocuments()
        ];
    }
}