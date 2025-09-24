<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Connection;

class AMQPClusterNode
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
    ) {
    }
}
