<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

class CalcMedianConsumer extends BaseStreamConsumer
{
    private array $values = [];

    public function handleMessage(AmqpMessage $message): void
    {
        $body = $message->getBody();
        $data = json_decode($body, true);

        if (isset($data['value']) && is_numeric($data['value'])) {
            $this->values[] = (float)$data['value'];
        }

        $message->ack();
    }

    protected function calcAndShowResult(): void
    {
        if (empty($this->values)) {
            echo "[!] No consuming messages found." . PHP_EOL;
            return;
        }

        sort($this->values);
        $count = count($this->values);

        if ($count % 2 === 0) {
            // Четное количество - среднее двух средних элементов
            $median = ($this->values[$count / 2 - 1] + $this->values[$count / 2]) / 2;
        } else {
            // Нечетное количество - средний элемент
            $median = $this->values[intval($count / 2)];
        }

        echo "[!] The median of consuming messages: {$median}" . PHP_EOL;
    }
}
