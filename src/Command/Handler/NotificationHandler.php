<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command\Handler;

use Alogachev\Homework\Rabbit\Consumer\ConsumerInterface;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotificationHandler extends Command
{
    public function __construct(
        private readonly ConsumerInterface $smsConsumer,
        private readonly ConsumerInterface $emailConsumer,
    ) {
        parent::__construct('app:handler:handle-notification');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $output->writeln("Start consuming $type orders");

        try {
            match ($type) {
                'sms' => $this->smsConsumer->consume(),
                'email' => $this->emailConsumer->consume(),
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
        $this->addArgument('type', InputArgument::REQUIRED, 'Notification type');
    }
}
