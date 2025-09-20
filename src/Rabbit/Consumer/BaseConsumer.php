<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use Alogachev\Homework\Rabbit\Connection\AMQPRabbitConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class BaseConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly string               $queueName,
        private readonly AMQPRabbitConnection $connection,
    ) {
    }

    public function consume(): void
    {
        $channel = $this->connection->getChannel();
        $channel->basic_consume(
            queue: $this->queueName,
            no_ack: false,
            callback: [$this, 'handleMessage']
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }

    abstract public function handleMessage(AmqpMessage $message): void;
}
