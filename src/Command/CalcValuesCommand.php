<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command;

use Alogachev\Homework\Rabbit\Publisher\PublisherInterface;

class CalcValuesCommand extends BaseSendCommand
{
    protected const string START_OUTPUT = 'Start to send values for a calculation';
    protected const string ERROR_OUTPUT = "Couldn't send messages: ";

    public function __construct(
        string             $pathToMessages,
        PublisherInterface $publisher,
    ) {
        parent::__construct($pathToMessages, $publisher, 'app:send-calc-values');
    }
}
