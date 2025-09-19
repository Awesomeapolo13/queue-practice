<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

class CalcAvgConsumer extends BaseStreamConsumer
{
    private int $count = 0;
    private float $sum = 0;

    public function handleMessage(AmqpMessage $message): void
    {
        $body = $message->getBody();
        $data = json_decode($body, true);
        $value = $data['value'] ?? 0;
        $this->count++;
        $this->sum += $value;
        $message->ack();
    }

    protected function calcAndShowResult(): void
    {
        if ($this->count === 0) {
            echo "[!] No consuming messages found." . PHP_EOL;
        } else {
            $avg = $this->sum / $this->count;
            echo "[!] The average of consuming messages: {$avg}" . PHP_EOL;
        }
    }
}
