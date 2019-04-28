<?php
/**
 * User: Wajdi Jurry
 * Date: 16/03/19
 * Time: 01:11 م
 */

namespace Shop_products\Modules\Cli\Tasks;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Shop_products\Enums\QueueNamesEnum;
use Shop_products\Logger\ApplicationLogger;
use Shop_products\Modules\Cli\Request\Handler as RequestHandler;

class ConsumerTask extends MainTask
{

    const SYNC_QUEUE_NAME = QueueNamesEnum::PRODUCT_SYNC_QUEUE;
    const ASYNC_QUEUE_NAME = QueueNamesEnum::PRODUCT_ASYNC_QUEUE;

    /** @var ApplicationLogger */
    private $logger;

    public function onConstruct()
    {
        $this->logger = new ApplicationLogger();
    }

    public function syncConsumerAction()
    {
        /** @var AMQPChannel $channel */
        $channel = $this->getDI()->get('queue');

        try {
            $channel->basic_qos(null, 1, null);
            $channel->basic_consume(self::SYNC_QUEUE_NAME,
                '', false, false, false, false,
                function (AMQPMessage $request) use ($channel) {
                    $payload = json_decode($request->getBody(), true);
                    /** @var AMQPChannel $amqpRequest */
                    $amqpRequest = $request->delivery_info['channel'];
                    try {

                        // handle request
                        $response = (new RequestHandler($payload['service'], $payload['service_args'], $payload['action'], $payload['data']))
                            ->call();

                        // send response
                        $message = json_encode($response);

                    } catch (\Throwable $exception) {
                        $this->logger->logError($exception->getMessage());
                        $message = json_encode([
                            'hasError' => true,
                            'message' => $exception->getMessage(),
                            'code' => $exception->getCode() ?: 500
                        ]);
                    }
                    $amqpRequest->basic_ack($request->delivery_info['delivery_tag']);
                    $amqpRequest->basic_publish(new AMQPMessage($message, [
                        'correlation_id' => $request->get('correlation_id'),
                        'reply_to' => $request->get('reply_to')
                    ]), '', $request->get('reply_to'));
                }
            );

            while (count($channel->callbacks)) {
                $channel->wait();
            }

        } catch (\Throwable $exception) {
            $this->logger->logError($exception->getMessage());
        }
    }

    public function asyncConsumerAction()
    {
        /** @var AMQPChannel $channel */
        $channel = $this->getDI()->get('queue');

        try {
            $channel->basic_qos(null, 1, null);
            $channel->basic_consume(self::ASYNC_QUEUE_NAME, '', false, true, false, false,
                function (AMQPMessage $message) {
                    $payload = json_decode($message->getBody(), true);
                    (new RequestHandler($payload['service'], $payload['service_args'], $payload['method'], $payload['data']))
                        ->call();
                }
            );

            while (count($channel->callbacks)) {
                $channel->wait();
            }
        } catch (\Throwable $exception) {
            $this->logger->logError($exception->getMessage());
        }
    }
}