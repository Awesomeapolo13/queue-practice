<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

class DirectOrderCreatedConsumer extends BaseConsumer
{
    public function handleMessage(AmqpMessage $message): void
    {
        $body = $message->getBody();
        $data = json_decode($body, true);
        echo "Got an order with {$data['id']} from {$data['orderDate']} with comment {$data['comment']}" . PHP_EOL;
    }
}
