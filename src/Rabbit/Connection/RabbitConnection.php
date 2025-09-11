<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Connection;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitConnection
{
    private readonly AMQPStreamConnection $connection;

    /**
     * @throws \Exception
     */
    public function __construct(
        string $rabbitHost,
        int $rabbitPort,
        string $rabbitUser,
        string $rabbitPassword,
    ) {
        $this->connection = new AMQPStreamConnection(
            $rabbitHost,
            $rabbitPort,
            $rabbitUser,
            $rabbitPassword,
        );
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    public function getChannel(): AMQPChannel
    {
        return $this->connection->channel();
    }

    /**
     * @throws \Exception
     */
    public function close(): void
    {
        $this->connection->close();
    }
}
