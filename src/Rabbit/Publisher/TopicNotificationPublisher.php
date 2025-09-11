<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Publisher;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class TopicNotificationPublisher extends BasePublisher
{
    private const string BASIC_ROUTING_KEY = 'notification.%s.*';

    protected function sendInBatch(AMQPChannel $channel, array $message): void
    {
        $routingKey = sprintf(self::BASIC_ROUTING_KEY, $message['type']);
        $encodedMessage = json_encode($message, JSON_THROW_ON_ERROR);
        $channel->batch_basic_publish(
            new AMQPMessage($encodedMessage),
            $this->exchangeName,
            $routingKey
        );
    }
}
