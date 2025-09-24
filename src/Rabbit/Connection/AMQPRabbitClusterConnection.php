<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Connection;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class AMQPRabbitClusterConnection implements AMQPRabbitConnectionInterface
{
    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;

    /**
     * @param AMQPClusterNode[] $clusterConnectionParams
     */
    public function __construct(
        private readonly string $username = 'guest',
        private readonly string $password = 'guest',
        private readonly string $vhost = '/',
        private readonly array $clusterConnectionParams = [],
    ) {
    }

    public function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null || !$this->connection->isConnected()) {
            $this->connection = $this->createConnection();
        }

        return $this->connection;
    }

    public function getChannel(): AMQPChannel
    {
        if ($this->channel === null || !$this->channel->is_open()) {
            $connection = $this->getConnection();
            $this->channel = $connection->channel();
        }

        return $this->channel;
    }

    /**
     * @throws \Exception
     */
    private function createConnection(): AMQPStreamConnection
    {
        $hosts = $this->clusterConnectionParams;
        shuffle($hosts);

        foreach ($hosts as $host) {
            return new AMQPStreamConnection(
                host: $host->host,
                port: $host->port,
                user: $this->username,
                password: $this->password,
                vhost: $this->vhost,
                keepalive: true,
                heartbeat: 60,
            );
        }

        throw new \RuntimeException('Could create connection');
    }
}
