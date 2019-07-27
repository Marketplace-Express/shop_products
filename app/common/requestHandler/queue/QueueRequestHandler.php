<?php
/**
 * User: Wajdi Jurry
 * Date: 16/02/19
 * Time: 06:27 م
 */

namespace app\common\requestHandler\queue;


use Phalcon\Di\Injectable;
use Phalcon\Validation;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use app\common\exceptions\ArrayOfStringsException;

class QueueRequestHandler extends Injectable
{

    const REQUEST_TYPE_SYNC = 'sync';
    const REQUEST_TYPE_ASYNC = 'async';

    private $queueName;
    private $service;
    private $method;
    private $data;
    private $serviceArgs = [];
    private $correlationId;
    private $replyTo = null;
    private $exchange = null;

    /** @var AMQPChannel */
    private $channel;

    /** @var AMQPMessage */
    private $response;

    /** @var string */
    private $requestType;

    /**
     * @return string
     */
    private function getCorrelationId(): string
    {
        return $this->correlationId = uniqid('', true);
    }

    /**
     * @param string $queueName
     * @return QueueRequestHandler
     */
    public function setQueueName(string $queueName)
    {
        $this->queueName = $queueName;
        return $this;
    }

    /**
     * @param string $service
     * @return QueueRequestHandler
     */
    public function setService(string $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @param string $method
     * @return QueueRequestHandler
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param array $data
     * @return QueueRequestHandler
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param array $args
     * @return QueueRequestHandler
     */
    public function setServiceArgs(array $args = [])
    {
        $this->serviceArgs = $args;
        return $this;
    }

    /**
     * @param string $exchange
     * @return QueueRequestHandler
     */
    public function setExchange(string $exchange)
    {
        $this->exchange = $exchange;
        return $this;
    }

    /**
     * QueueRequestHandler constructor.
     * @param $requestType
     */
    public function __construct($requestType)
    {
        $this->requestType = $requestType;
        $this->channel = $this->getDI()->get('queue');
        if ($requestType == self::REQUEST_TYPE_SYNC) {
            $this->queueName = \Phalcon\Di::getDefault()->getConfig()
                ->rabbitmq->rpc->queue_name;
            list($this->replyTo, ,) = $this->channel->queue_declare('', false, true, true, true);
        }
    }

    /**
     * @return bool
     * @throws ArrayOfStringsException
     * @throws \Exception
     */
    private function validate(): bool
    {
        $validator = new Validation();

        $validator->add(
            ['queueName', 'service', 'method'],
            new Validation\Validator\PresenceOf()
        );

        $messages = $validator->validate([
            'queueName' => $this->queueName,
            'service' => $this->service,
            'method' => $this->method
        ]);

        if (!count($messages)) {
            return true;
        }

        $errors = [];
        foreach ($messages as $message) {
            $errors[$message->getField()] = $message->getMessage();
        }

        if ($this->requestType == self::REQUEST_TYPE_ASYNC) {
            \Phalcon\Di::getDefault()->get('logger')->logError($errors);
        } elseif ($this->requestType == self::REQUEST_TYPE_SYNC) {
            throw new ArrayOfStringsException($errors, 400);
        } else {
            throw new \Exception('Unknown request type', 400);
        }

        return true;
    }

    /**
     * Initialize consumer for Sync requests
     * @throws \Exception
     */
    private function initializeConsumer(): void
    {
        if (empty($this->replyTo)) {
            throw new \Exception('Property "reply_to" is missing');
        }
        $this->channel->basic_consume($this->replyTo, '', false, true, false, false, [
            $this,
            'getResponse'
        ]);
    }

    /**
     * Wait response for Sync requests
     * @throws \ErrorException
     */
    private function waitResponse(): void
    {
        while (!isset($this->response)) {
            $this->channel->wait(null, false, 10);
        }
    }

    /**
     * @param AMQPMessage $response
     * @throws \Exception
     */
    public function getResponse($response)
    {
        if ($response->get('correlation_id') == $this->correlationId) {
            $this->response = json_decode($response->getBody(), true);
            if (array_key_exists('hasError', $this->response) && $this->response['hasError']) {
                $this->channel->basic_ack($response->delivery_info['delivery_tag']);
                throw new \Exception($this->response['message'], $this->response['code']);
            }
        }

    }

    /**
     * Send sync request to another endpoint
     * and waiting response
     *
     * @return mixed
     *
     * @throws \ErrorException
     * @throws \Exception
     */
    public function sendSync()
    {
        // validate request
        $this->validate();

        $this->initializeConsumer();
        $message = new AMQPMessage(json_encode([
            'service' => $this->service,
            'action' => $this->method,
            'data' => $this->data,
            'service_args' => $this->serviceArgs
        ]), [
            'reply_to' => $this->replyTo,
            'correlation_id' => $this->getCorrelationId(),
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);
        $this->channel->basic_publish($message, $this->exchange, $this->queueName);

        // Waiting response
        $this->waitResponse();

        // Return response
        return $this->response;
    }

    /**
     * @throws ArrayOfStringsException
     */
    public function sendAsync()
    {
        // validate request
        $this->validate();

        $message = new AMQPMessage(json_encode([
            'service' => $this->service,
            'service_args' => $this->serviceArgs,
            'method' => $this->method,
            'data' => $this->data
        ]));
        $this->channel->basic_publish($message, $this->exchange, $this->queueName);
    }
}