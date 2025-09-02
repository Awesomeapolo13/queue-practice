<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Publisher;

use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

abstract class BasePublisher implements PublisherInterface
{
    public function __construct(
        protected readonly string $exchangeName,
        protected readonly RabbitConnection $connection,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function publish(array ...$messages): void
    {
        $channel = $this->connection->getChannel();
        // Make a batch
        foreach ($messages as $message) {
            $this->sendInBatch($channel, $message);
        }

        $channel->publish_batch();
        $channel->close();
    }

    protected function sendInBatch(AMQPChannel $channel, array $message): void
    {
        $encodedMessage = json_encode($message, JSON_THROW_ON_ERROR);
        $channel->batch_basic_publish(
            new AMQPMessage($encodedMessage),
            $this->exchangeName,
        );
    }
}
