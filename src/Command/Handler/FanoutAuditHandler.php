<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command\Handler;

use Alogachev\Homework\Rabbit\Consumer\ConsumerInterface;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FanoutAuditHandler extends Command
{
    public function __construct(
        private readonly ConsumerInterface $auditConsumer,
        private readonly ConsumerInterface $monitoringConsumer,
        private readonly ConsumerInterface $backupConsumer,
    ) {
        parent::__construct('app:handler:handle-audit');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $output->writeln("Start consuming $type notifications");

        try {
            match ($type) {
                'audit' => $this->auditConsumer->consume(),
                'monitoring' => $this->monitoringConsumer->consume(),
                'backup' => $this->backupConsumer->consume(),
                default => throw new \RuntimeException('Unknown notification type'),
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
        $this->addArgument('type', InputArgument::REQUIRED, 'Message type');
    }
}
