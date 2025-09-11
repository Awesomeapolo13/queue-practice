<?php

declare(strict_types=1);

namespace Alogachev\Homework\Rabbit\Publisher;

use Alogachev\Homework\Rabbit\Connection\RabbitConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class NotificationWithReplyToPublisher implements PublisherInterface
{
    private const string PUBLISH_QUEUE = 'rpc_queue';
    private const string REPLY_TO_QUEUE_NAME = 'rpc_reply_to_queue';
    /**
     * @var string[]
     */
    private array $correlationIdsWithReply = [];

    public function __construct(
        protected readonly RabbitConnection $connection,
    ) {
        $channel = $this->connection->getChannel();
        $channel->basic_consume(
            queue: self::REPLY_TO_QUEUE_NAME,
            no_ack: true,
            callback: [
                $this,
                'onResponse'
            ]
        );
    }

    /**
     * @throws \JsonException
     */
    public function publish(array ...$messages): void
    {
        $channel = $this->connection->getChannel();
        // Make a batch
        foreach ($messages as $message) {
            $this->sendInBatch($channel, $message);
        }

        $channel->publish_batch();

        while (!$this->isAllRepliesGot()) {
            $channel->wait();
        }

        $channel->close();
    }

    public function onResponse(AMQPMessage $rep): void
    {
        $correlationId = (string) $rep->get('correlation_id');
        echo "CorrelationId: {$correlationId}" . PHP_EOL;
        if (
            isset($this->correlationIdsWithReply[$correlationId])
            && $this->correlationIdsWithReply[$correlationId] = ''
        ) {
            $reply = $rep->getBody();
            $this->correlationIdsWithReply[$correlationId] = $reply;

            echo "Got reply from message {$correlationId}: {$reply}" . PHP_EOL;
        }
    }

    protected function sendInBatch(AMQPChannel $channel, array $message): void
    {
        $correlationId = uniqid(more_entropy: true);
        $encodedMessage = json_encode($message, JSON_THROW_ON_ERROR);
        $channel->batch_basic_publish(
            message: new AMQPMessage(
                $encodedMessage,
                [
                    'correlation_id' => $correlationId,
                    'reply_to' => self::REPLY_TO_QUEUE_NAME,
                ],
            ),
            routing_key: self::PUBLISH_QUEUE
        );
        $this->correlationIdsWithReply[$correlationId] = '';
    }

    private function isAllRepliesGot(): bool
    {
        return array_all($this->correlationIdsWithReply, static fn($reply) => $reply !== '');
    }
}
