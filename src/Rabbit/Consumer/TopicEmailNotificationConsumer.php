<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use Alogachev\Homework\Rabbit\Connection\AMQPRabbitConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TopicEmailNotificationConsumer extends BaseConsumer
{
    public function handleMessage(AMQPMessage $message): void
    {
        $body = $message->getBody();
        $data = json_decode($body, true);
        echo "The new email notification from {$data['service']}: {$data['message']}" . PHP_EOL;
    }
}
