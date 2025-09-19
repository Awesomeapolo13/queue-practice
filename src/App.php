<?php

declare(strict_types=1);

namespace Alogachev\Homework;

use Alogachev\Homework\Command\Handler\FanoutAuditHandler;
use Alogachev\Homework\Command\Handler\NotificationHandler;
use Alogachev\Homework\Command\Handler\PrioritizedAnalyticHandler;
use Alogachev\Homework\Command\Handler\ReplyToHandler;
use Alogachev\Homework\Command\Handler\SentOrderHandler;
use Alogachev\Homework\Command\InitTopologyCommand;
use Alogachev\Homework\Command\ReplyToCommand;
use Alogachev\Homework\Command\SendAnalyticCommand;
use Alogachev\Homework\Command\SendAuditCommand;
use Alogachev\Homework\Command\SendNotificationsCommand;
use Alogachev\Homework\Command\SendOrdersCommand;
use Alogachev\Homework\Config\ConfigService;
use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use Alogachev\Homework\Rabbit\Consumer\DirectOrderCreatedConsumer;
use Alogachev\Homework\Rabbit\Consumer\FanoutAuditConsumer;
use Alogachev\Homework\Rabbit\Consumer\FanoutBackupConsumer;
use Alogachev\Homework\Rabbit\Consumer\FanoutMonitoringConsumer;
use Alogachev\Homework\Rabbit\Consumer\HighPriorityAnalyticConsumer;
use Alogachev\Homework\Rabbit\Consumer\NormalPriorityAnalyticConsumer;
use Alogachev\Homework\Rabbit\Consumer\NotificationWithReplyToConsumer;
use Alogachev\Homework\Rabbit\Consumer\TopicEmailNotificationConsumer;
use Alogachev\Homework\Rabbit\Consumer\TopicSMSNotificationConsumer;
use Alogachev\Homework\Rabbit\Publisher\DirectOrderCreatedEventPublisher;
use Alogachev\Homework\Rabbit\Publisher\FanoutAuditPublisher;
use Alogachev\Homework\Rabbit\Publisher\HeadersAnalyticPublisher;
use Alogachev\Homework\Rabbit\Publisher\NotificationWithReplyToPublisher;
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
            // Direct exchange
            DirectOrderCreatedEventPublisher::class => create()->constructor(
                $topology['bindings'][0]['routing_key'],
                $topology['bindings'][0]['exchange'],
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
            // Topic exchange
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
            // Headers exchange
            HeadersAnalyticPublisher::class => create()->constructor(
                $topology['bindings'][3]['exchange'],
                get(RabbitConnection::class)
            ),
            HighPriorityAnalyticConsumer::class => create()->constructor(
                $topology['bindings'][3]['queue'],
                get(RabbitConnection::class)
            ),
            NormalPriorityAnalyticConsumer::class => create()->constructor(
                $topology['bindings'][4]['queue'],
                get(RabbitConnection::class)
            ),
            PrioritizedAnalyticHandler::class => create()->constructor(
                get(HighPriorityAnalyticConsumer::class),
                get(NormalPriorityAnalyticConsumer::class)
            ),
            SendAnalyticCommand::class => create()->constructor(
                __DIR__ . '/../config/data/headers_analytics.json',
                get(HeadersAnalyticPublisher::class)
            ),
            // Fanout exchange
            FanoutAuditPublisher::class => create()->constructor(
                $topology['bindings'][5]['exchange'],
                get(RabbitConnection::class)
            ),
            FanoutAuditConsumer::class => create()->constructor(
                $topology['bindings'][5]['queue'],
                get(RabbitConnection::class)
            ),
            FanoutMonitoringConsumer::class => create()->constructor(
                $topology['bindings'][6]['queue'],
                get(RabbitConnection::class)
            ),
            FanoutBackupConsumer::class => create()->constructor(
                $topology['bindings'][7]['queue'],
                get(RabbitConnection::class)
            ),
            FanoutAuditHandler::class => create()->constructor(
                get(FanoutAuditConsumer::class),
                get(FanoutMonitoringConsumer::class),
                get(FanoutBackupConsumer::class)
            ),
            SendAuditCommand::class => create()->constructor(
                __DIR__ . '/../config/data/audit_broadcast.json',
                get(FanoutAuditPublisher::class)
            ),
            // Reply-to
            NotificationWithReplyToPublisher::class => create()->constructor(
                get(RabbitConnection::class)
            ),
            NotificationWithReplyToConsumer::class => create()->constructor(
                'rpc_queue',
                get(RabbitConnection::class)
            ),
            ReplyToHandler::class => create()->constructor(
                get(NotificationWithReplyToConsumer::class),
            ),
            ReplyToCommand::class => create()->constructor(
                __DIR__ . '/../config/data/topic_notifications.json',
                get(NotificationWithReplyToPublisher::class)
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

        $this->application->add(
            $this->container->get(SendAnalyticCommand::class),
        );
        $this->application->add(
            $this->container->get(PrioritizedAnalyticHandler::class),
        );

        $this->application->add(
            $this->container->get(SendAuditCommand::class),
        );
        $this->application->add(
            $this->container->get(FanoutAuditHandler::class),
        );

        $this->application->add(
            $this->container->get(ReplyToCommand::class),
        );
        $this->application->add(
            $this->container->get(ReplyToHandler::class),
        );
    }
}
