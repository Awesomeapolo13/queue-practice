<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

class FanoutMonitoringConsumer extends BaseConsumer
{
    public function handleMessage(AMQPMessage $message): void
    {
        $body = $message->getBody();
        $data = json_decode($body, true);
        echo "Got a monitoring event {$data['event_name']}." . PHP_EOL;
    }
}
