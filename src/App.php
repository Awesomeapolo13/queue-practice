<?php

declare(strict_types=1);

namespace Alogachev\Homework;

use Alogachev\Homework\Command\Handler\NotificationHandler;
use Alogachev\Homework\Command\Handler\SentOrderHandler;
use Alogachev\Homework\Command\InitTopologyCommand;
use Alogachev\Homework\Command\SendNotificationsCommand;
use Alogachev\Homework\Command\SendOrdersCommand;
use Alogachev\Homework\Config\ConfigService;
use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use Alogachev\Homework\Rabbit\Consumer\DirectOrderCreatedConsumer;
use Alogachev\Homework\Rabbit\Consumer\TopicEmailNotificationConsumer;
use Alogachev\Homework\Rabbit\Consumer\TopicSMSNotificationConsumer;
use Alogachev\Homework\Rabbit\Publisher\DirectOrderCreatedEventPublisher;
use Alogachev\Homework\Rabbit\Publisher\TopicNotificationPublisher;
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
            ),

            DirectOrderCreatedEventPublisher::class => create()->constructor(
                $topology['bindings'][0]['exchange'],
                $topology['bindings'][0]['routing_key'],
                get(RabbitConnection::class),
            ),
            SendOrdersCommand::class => create()->constructor(
                __DIR__ . '/../config/data/direct_order.json',
                get(DirectOrderCreatedEventPublisher::class)
            ),
            DirectOrderCreatedConsumer::class => create()->constructor(
                $topology['bindings'][0]['queue'],
                get(RabbitConnection::class)
            ),
            SentOrderHandler::class => create()->constructor(
                get(DirectOrderCreatedConsumer::class)
            ),

            TopicNotificationPublisher::class => create()->constructor(
                $topology['bindings'][1]['exchange'],
                get(RabbitConnection::class)
            ),
            TopicEmailNotificationConsumer::class => create()->constructor(
                $topology['bindings'][1]['queue'],
                get(RabbitConnection::class)
            ),
            TopicSMSNotificationConsumer::class => create()->constructor(
                $topology['bindings'][2]['queue'],
                get(RabbitConnection::class)
            ),
            NotificationHandler::class => create()->constructor(
                get(TopicSMSNotificationConsumer::class),
                get(TopicEmailNotificationConsumer::class)
            ),
            SendNotificationsCommand::class => create()->constructor(
                __DIR__ . '/../config/data/topic_notifications.json',
                get(TopicNotificationPublisher::class)
            ),
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

        $this->application->add(
            $this->container->get(SendOrdersCommand::class)
        );
        $this->application->add(
            $this->container->get(SentOrderHandler::class)
        );

        $this->application->add(
            $this->container->get(SendNotificationsCommand::class),
        );
        $this->application->add(
            $this->container->get(NotificationHandler::class),
        );
    }
}
