<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command;

use Alogachev\Homework\Rabbit\Publisher\PublisherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendNotificationsCommand extends Command
{
    public function __construct(
        private readonly string             $pathToMessages,
        private readonly PublisherInterface $publisher,
    ) {
        parent::__construct('app:send-notifications');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Start sending notifications");
        try {
            $data = file_get_contents($this->pathToMessages);
            $messages = json_decode($data, true);
            $this->publisher->publish(...$messages);
        } catch (\Throwable $exception) {
            $output->writeln("Couldn't send messages: " . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
