<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Publisher;

use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TopicNotificationPublisher implements PublisherInterface
{
    private const string BASIC_ROUTING_KEY = 'notification.%s.*';
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
            $routingKey = sprintf(self::BASIC_ROUTING_KEY, $message['type']);
            $message = json_encode($message, JSON_THROW_ON_ERROR);
            $channel->batch_basic_publish(
                new AMQPMessage($message),
                $this->exchangeName,
                $routingKey
            );
        }

        $channel->publish_batch();
        $channel->close();
    }
}
