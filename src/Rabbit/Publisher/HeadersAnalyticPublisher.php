<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Publisher;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class HeadersAnalyticPublisher extends BasePublisher
{
    protected function sendInBatch(AMQPChannel $channel, array $message): void
    {
        $headers = new AMQPTable([
            'priority' => $message['priority'] ?? 'low',
            'x-match' => 'any',
        ]);

        $encodedMessage = json_encode($message, JSON_THROW_ON_ERROR);
        $channel->batch_basic_publish(
            new AMQPMessage(
                $encodedMessage,
                [
                    'application_headers' => $headers,
                    'x-match' => 'any',
                ]
            ),
            $this->exchangeName,
        );
    }
}
