<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command\Handler;

use Alogachev\Homework\Rabbit\Consumer\ConsumerInterface;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplyToHandler extends Command
{
    public function __construct(
        private readonly ConsumerInterface $consumer,
    ) {
        parent::__construct('app:handler:reply-to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Start consuming notifications with rely-to responces");
        try {
            $this->consumer->consume();
        } catch (AMQPTimeoutException $exception) {
            $output->writeln("Consuming stoped after timeout: " . $exception->getMessage());
        } catch (\Throwable $exception) {
            $output->writeln("Couldn't handle an notification: " . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
