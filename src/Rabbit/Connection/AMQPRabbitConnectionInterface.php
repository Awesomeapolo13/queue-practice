<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Connection;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

interface AMQPRabbitConnectionInterface
{
    public function getConnection(): AMQPStreamConnection;
    public function getChannel(): AMQPChannel;
}
