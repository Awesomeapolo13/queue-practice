<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use PhpAmqpLib\Message\AMQPMessage;

class HighPriorityAnalyticConsumer extends BaseConsumer
{
    public function handleMessage(AMQPMessage $message): void
    {
        $body = $message->getBody();
        $data = json_decode($body, true);
        if ($data['is_error']) {
            echo 'IMPORTANT ERROR: ';
        }

        echo "Got an {$data['type']} event {$data['name']}." . PHP_EOL;
    }
}
