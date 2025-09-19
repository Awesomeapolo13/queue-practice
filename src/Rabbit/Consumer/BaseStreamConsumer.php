<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

abstract class BaseStreamConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly string $streamName,
        private readonly int $timeout,
        private readonly RabbitConnection $connection,
    ) {
    }

    public function consume(): void
    {
        $channel = $this->connection->getChannel();

        // read 5 messages
        $channel->basic_qos(
            prefetch_size: 0,
            prefetch_count: 5,
            a_global: false
        );
        $channel->basic_consume(
            queue: $this->streamName,
            callback: [$this, 'handleMessage'],
            arguments: new AMQPTable([
                'x-stream-offset' => 'first'
            ])
        );
        while ($channel->is_consuming()) {
            try {
                $channel->wait(timeout: $this->timeout);
            } catch (AMQPTimeoutException $exception) {
                echo "[!] Timeout reached. Stopping and show the result" . PHP_EOL;
                break;
            }
        }

        $this->calcAndShowResult();

        $channel->close();
    }

    abstract public function handleMessage(AmqpMessage $message): void;
    abstract protected function calcAndShowResult(): void;
}
