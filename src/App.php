<?php

declare(strict_types=1);

namespace Alogachev\Homework;

use Alogachev\Homework\Command\InitTopologyCommand;
use Alogachev\Homework\Config\ConfigService;
use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use DI\Container;
use Dotenv\Dotenv;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Application;

use function DI\create;
use function DI\get;

class App
{
    private readonly Application $application;
    private readonly ContainerInterface $container;

    public function __construct()
    {
        $this->application = new Application();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function runConsole(): void
    {
        $this->loadEnv();
        $this->loadDI();
        $this->loadCommands();
        $this->application->run();
    }

    private function loadEnv(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    }

    private function loadDI(): void
    {
        $rabbitHost = $_ENV['RABBIT_HOST'] ?? '';
        $rabbitPort = $_ENV['RABBIT_PORT'] ?? '';
        $rabbitUser = $_ENV['RABBIT_USER'] ?? '';
        $rabbitPassword = $_ENV['RABBIT_PASSWORD'] ?? '';
        $configService = new ConfigService();
        $topology = $configService->get('rabbitmq/topology');

        $this->container = new Container([
            RabbitConnection::class => create()->constructor(
                $rabbitHost,
                (int) $rabbitPort,
                $rabbitUser,
                $rabbitPassword
            ),
            InitTopologyCommand::class => create()->constructor(
                $topology,
                get(RabbitConnection::class)
            )
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function loadCommands(): void
    {
        $this->application->add(
            $this->container->get(InitTopologyCommand::class)
        );
    }
}
