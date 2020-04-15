<?php
/**
 * User: Wajdi Jurry
 * Date: 17/03/19
 * Time: 10:16 م
 */

namespace app\common\redis;


use Ehann\RedisRaw\PhpRedisAdapter;
use Ehann\RedisRaw\RedisRawClientInterface;

/**
 * Class Connector
 * @package app\common\redis
 *
 * Implement Ehann\RedisRaw\PhpRedisAdapter
 * To enable persistent connection To Redis
 */
class Connector extends PhpRedisAdapter
{
    public function connect($hostname = '127.0.0.1', $port = 6379, $db = 0, $password = null): RedisRawClientInterface
    {
        $this->redis = new \Redis();
        $this->redis->pconnect($hostname, $port);
        $this->redis->select($db);
        $this->redis->auth($password);
        return $this;
    }
}
