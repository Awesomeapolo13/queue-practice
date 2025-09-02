<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command;

use Alogachev\Homework\Rabbit\Publisher\PublisherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseSendCommand extends Command
{
    protected const string START_OUTPUT = "";
    protected const string ERROR_OUTPUT = "";

    public function __construct(
        protected readonly string             $pathToMessages,
        protected readonly PublisherInterface $publisher,
        string $name,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(static::START_OUTPUT);
        try {
            $data = file_get_contents($this->pathToMessages);
            $messages = json_decode($data, true);
            $this->publisher->publish(...$messages);
        } catch (\Throwable $exception) {
            $output->writeln(static::ERROR_OUTPUT . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
