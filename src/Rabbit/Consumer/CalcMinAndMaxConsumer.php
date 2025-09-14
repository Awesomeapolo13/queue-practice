<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

class CalcMinAndMaxConsumer extends BaseStreamConsumer
{
    private int $count = 0;
    private ?float $min = null;
    private ?float $max = 0;

    public function handleMessage(AmqpMessage $message): void
    {
        $body = $message->getBody();
        $data = json_decode($body, true);
        $value = $data['value'] ?? 0;
        $this->count++;
        if ($this->min === null) {
            $this->min = $value;
        }
        if ($this->max === null) {
            $this->max = $value;
        }

        if ($value < $this->min) {
            $this->min = $value;
        }
        if ($value > $this->max) {
            $this->max = $value;
        }
        $message->ack();
    }

    protected function calcAndShowResult(): void
    {
        if ($this->count === 0) {
            echo "[!] No consuming messages found." . PHP_EOL;
        } else {
            echo "[!] The min value is {$this->min}" . PHP_EOL;
            echo "[!] The max value is {$this->max}" . PHP_EOL;
        }
    }
}
