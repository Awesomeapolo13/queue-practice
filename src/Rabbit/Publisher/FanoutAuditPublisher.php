<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Publisher;

use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FanoutAuditPublisher implements PublisherInterface
{
    public function __construct(
        private readonly string $exchangeName,
        private readonly RabbitConnection $connection,
    ) {
    }

    public function publish(array ...$messages): void
    {
        $channel = $this->connection->getChannel();
        // Make a batch
        foreach ($messages as $message) {
            $message = json_encode($message, JSON_THROW_ON_ERROR);
            $channel->batch_basic_publish(
                new AMQPMessage($message),
                $this->exchangeName,
            );
        }

        $channel->publish_batch();
        $channel->close();
    }
}
