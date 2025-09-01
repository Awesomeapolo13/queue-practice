<?php

declare(strict_types=1);

namespace Alogachev\Homework\Command;

use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitTopologyCommand extends Command
{
    public function __construct(
        private readonly array $topology,
        private readonly RabbitConnection $connection,
    ) {
        parent::__construct('app:init-topology');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Initiate a topology');
        $output->writeln('Create exchanges');

        if (!isset($this->topology['exchanges'], $this->topology['queues'], $this->topology['bindings'])) {
            $output->writeln('Topology does not exist');

            return Command::FAILURE;
        }

        try {
            $channel = $this->connection->getChannel();
            $this->createExchanges($channel, $output);
            $this->createQueues($channel, $output);
            $this->createBindings($channel, $output);
            $channel->close();
        } catch (\Throwable $exception) {
            $output->writeln("Couldn't initiate a topology: " . $exception->getMessage());

            return Command::FAILURE;
        }


        return Command::SUCCESS;
    }

    private function createExchanges(AMQPChannel $channel, OutputInterface $output): void
    {
        $exchanges = $this->topology['exchanges'];

        foreach ($exchanges as $name => $exchange) {
            $output->writeln("Creating exchange: {$name} (type: {$exchange['type']})");

            $channel->exchange_declare(
                exchange: $name,
                type: $exchange['type'],
                durable: $exchange['durable'],
                auto_delete: $exchange['auto_delete'],
            );
        }
    }

    private function createQueues(AMQPChannel $channel, OutputInterface $output): void
    {
        $queues = $this->topology['queues'];

        foreach ($queues as $name => $config) {
            $output->writeln("Creating queue: {$name}");

            $channel->queue_declare(
                queue: $name,
                durable: $config['durable'],
                exclusive: $config['exclusive'],
                auto_delete: $config['auto_delete']
            );
        }
    }

    private function createBindings(AMQPChannel $channel, OutputInterface $output): void
    {
        $bindings = $this->topology['bindings'];

        foreach ($bindings as $binding) {
            $queue = $binding['queue'];
            $exchange = $binding['exchange'];
            $routingKey = $binding['routing_key'];

            $output->write("Binding queue '{$queue}' to exchange '{$exchange}'");

            if (isset($binding['headers'])) {
                $output->write( " with headers: " . json_encode($binding['headers']));
            } else if ($routingKey !== '') {
                $output->write(" with routing key: '{$routingKey}'");
            }
            $channel->queue_bind(
                queue: $queue,
                exchange: $exchange,
                routing_key: $routingKey,
                arguments: new AMQPTable($binding['headers'] ?? [])
            );
        }
    }
}
