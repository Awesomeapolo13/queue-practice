<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use PhpAmqpLib\Message\AMQPMessage;

class NormalPriorityAnalyticConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly string $queueName,
        private readonly RabbitConnection $connection,
    ) {
    }

    public function consume(): void
    {
        $channel = $this->connection->getChannel();
        $channel->basic_consume(
            queue: $this->queueName,
            no_ack: true,
            callback: function (AmqpMessage $message) {
                $body = $message->getBody();
                $data = json_decode($body, true);
                if ($data['is_error']) {
                    echo 'USUAL ERROR: ';
                }

                echo "Got an {$data['type']} event {$data['name']}." . PHP_EOL;
            }
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
    }
}
