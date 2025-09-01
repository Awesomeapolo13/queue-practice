<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command\Handler;

use Alogachev\Homework\Rabbit\Consumer\ConsumerInterface;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrioritizedAnalyticHandler extends Command
{
    public function __construct(
        private readonly ConsumerInterface $highPriorityConsumer,
        private readonly ConsumerInterface $normalPriorityConsumer,
    ) {
        parent::__construct('app:handler:handle-analytics');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $priority = $input->getArgument('priority');
        $output->writeln("Start consuming analytics with $priority priority");
        try {
            match ($priority) {
                'high' => $this->highPriorityConsumer->consume(),
                'normal' => $this->normalPriorityConsumer->consume(),
                default => throw new \RuntimeException('Unknown priority'),
            };
        } catch (AMQPTimeoutException $exception) {
            $output->writeln("Consuming stoped after timeout: " . $exception->getMessage());
        } catch (\Throwable $exception) {
            $output->writeln("Couldn't handle an order: " . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('priority', InputArgument::REQUIRED, 'Priority');
    }
}
