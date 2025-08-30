<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Publisher;

interface PublisherInterface
{
    public function publish(array ...$messages): void;
}
