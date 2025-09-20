<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use Alogachev\Homework\Rabbit\Connection\MQTTRabbitConnection;
use PhpMqtt\Client\Exceptions\DataTransferException;
use PhpMqtt\Client\Exceptions\InvalidMessageException;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\Exceptions\ProtocolViolationException;
use PhpMqtt\Client\Exceptions\RepositoryException;

class SumCalculationConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly MQTTRabbitConnection $client,
    ) {
    }

    /**
     * @throws ProtocolViolationException
     * @throws InvalidMessageException
     * @throws MqttClientException
     * @throws RepositoryException
     * @throws DataTransferException
     */
    public function consume(): void
    {
        $sum = 0.0;
        $connection = $this->client->getConnection();
        $connection->subscribe('calculator/numbers', function (string $topic, string $message) use ($connection, &$sum): void {
            $number = (float) $message;
            $sum += $number;

            echo sprintf("Got a number: %.2f | The current sum: %.2f", $number, $sum) . PHP_EOL;

            // Публикуем новую сумму
            $connection->publish('calculator/sum', (string) $sum, 1);
        }, 1);

        $connection->subscribe('calculator/reset', function (string $topic, string $message) use ($connection, &$sum) {
            $sum = 0.0;

            echo "Сумма сброшена" . PHP_EOL;

            $connection->publish('calculator/sum', '0', 1);
        }, 1);

        $connection->loop(allowSleep: true);
    }
}
