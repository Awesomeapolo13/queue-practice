<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command\Handler;

use Alogachev\Homework\Rabbit\Consumer\ConsumerInterface;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateValuesHandler extends Command
{
    public function __construct(
        private readonly ConsumerInterface $streamAvgConsumer,
        private readonly ConsumerInterface $streamMedianConsumer,
        private readonly ConsumerInterface $minMaxConsumer,
    ) {
        parent::__construct('app:handler:calc');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $output->writeln("Start $type calculation");

        try {
            match ($type) {
                'avg' => $this->streamAvgConsumer->consume(),
                'median' => $this->streamMedianConsumer->consume(),
                'minmax' => $this->minMaxConsumer->consume(),
                default => throw new \RuntimeException('Unknown calculation type'),
            };
        } catch (AMQPTimeoutException $exception) {
            $output->writeln("Consuming stoped after timeout: " . $exception->getMessage());
        } catch (\Throwable $exception) {
            $output->writeln("Couldn't handle {$type} calculation: " . $exception->getMessage());
//            var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)) . PHP_EOL;

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'Operation type');
    }
}
