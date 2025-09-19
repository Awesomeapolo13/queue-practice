<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

class NotificationWithReplyToConsumer extends BaseConsumer
{
    public function handleMessage(AMQPMessage $message): void
    {
        $body = $message->getBody();
        $data = json_decode($body, true);

        echo "The new email notification from {$data['service']}: {$data['message']}" . PHP_EOL;

        $correlationId = $message->get('correlation_id');
        $replyTo = $message->get('reply_to');
        $reply = "The notification with {$correlationId} has been sent by {$data['type']} successfully";
//        echo "Reply to {$message->get('reply_to')}" . PHP_EOL;
//        echo $reply . PHP_EOL;
        if ($correlationId && $replyTo) {
            $message->getChannel()?->basic_publish(
                new AMQPMessage(
                    $reply,
                    ['correlation_id' => $correlationId]
                ),
                '',
                $replyTo
            );
            echo "Sent a reply to {$replyTo} with correlationId {$correlationId}" . PHP_EOL;
        }

        $message->ack();
    }
}
