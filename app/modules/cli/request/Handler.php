<?php
/**
 * User: Wajdi Jurry
 * Date: 22/02/19
 * Time: 04:40 م
 */

namespace app\modules\cli\request;


use GuzzleHttp\Client;
use Phalcon\Di\Injectable;

class Handler extends Injectable implements RequestHandlerInterface
{
    private static $instance;

    private $guzzleHttp;

    public function __construct()
    {
        $config = \Phalcon\Di::getDefault()->getConfig()->application;

        $this->guzzleHttp = new Client([
            'base_uri' => $config->api->base_uri,
            'timeout' => $config->api->timeout
        ]);
    }

    /**
     * @return static
     */
    static public function getInstance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }

    public function process(string $route = '', string $method = 'get', array $query = [], array $body = [], array $headers = [])
    {
        if (!empty($route)) {
            $request = $this->guzzleHttp->request($method, $route, [
                'json' => $body,
                'query' => $query,
                'headers' => $headers
            ]);

            return $request->getBody()->getContents();
        }

        return null;
    }
}