<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

interface ConsumerInterface
{
    public const int DEFAULT_WAITING_TIMEOUT = 10;
    public function consume(): void;
}
