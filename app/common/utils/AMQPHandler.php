<?php


namespace app\common\utils;


use Phalcon\Config;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

class AMQPHandler
{
    /** @var AMQPChannel */
    private $channel;

    /** @var Config */
    private $config;

    /**
     * QueuesHandler constructor.
     * @param AMQPChannel $channel
     * @param Config $config
     */
    public function __construct(AMQPChannel $channel, Config $config)
    {
        $this->channel = $channel;
        $this->config = $config;
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    public function declareSync()
    {
        $this->channel->queue_declare(
            $this->config->rabbitmq->sync_queue->queue_name,
            false, false, false, false, false,
            new AMQPTable(['x-message-ttl' => $this->config->rabbitmq->sync_queue->message_ttl])
        );
    }

    public function declareAsync()
    {
        $this->channel->queue_declare(
            $this->config->rabbitmq->async_queue->queue_name,
            false, false, false, false, false,
            new AMQPTable(['x-message-ttl' => $this->config->rabbitmq->async_queue->message_ttl])
        );
    }
}