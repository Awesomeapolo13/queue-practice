<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Publisher;

use Alogachev\Homework\Rabbit\Connection\AMQPRabbitConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class CalcValuePublisher extends BasePublisher
{
    public function __construct(
        private readonly string $routingKey,
        string                  $exchangeName,
        AMQPRabbitConnection    $connection,
    ) {
        parent::__construct($exchangeName, $connection);
    }

    protected function sendInBatch(AMQPChannel $channel, array $message): void
    {
        $encodedMessage = json_encode($message, JSON_THROW_ON_ERROR);
        $channel->batch_basic_publish(
            new AMQPMessage(
                $encodedMessage,
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => 2, // persistent
                    'timestamp' => time()
                ]
            ),
            $this->exchangeName,
            $this->routingKey
        );
    }
}
