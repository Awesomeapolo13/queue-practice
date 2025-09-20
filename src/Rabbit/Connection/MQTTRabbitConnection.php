<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Connection;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\ConfigurationInvalidException;
use PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException;
use PhpMqtt\Client\Exceptions\ProtocolNotSupportedException;
use PhpMqtt\Client\MqttClient;

class MQTTRabbitConnection
{
    private MqttClient $mqttClient;

    /**
     * @throws ProtocolNotSupportedException
     * @throws ConfigurationInvalidException
     * @throws ConnectingToBrokerFailedException
     */
    public function __construct(
        string $host,
        int $port,
        string $userName,
        string $password,
    ) {
        $clientId = 'php-calculator-' . uniqid('', true);
        $connectionSettings = new ConnectionSettings()
            ->setUsername($userName)
            ->setPassword($password)
            ->setKeepAliveInterval(60)
            ->setUseTLS(false);
        $this->mqttClient = new MqttClient(
            host: $host,
            port: $port,
            clientId: $clientId,
        );
        $this->mqttClient->connect($connectionSettings, true);
    }

    public function getConnection(): MqttClient
    {
        return $this->mqttClient;
    }
}
