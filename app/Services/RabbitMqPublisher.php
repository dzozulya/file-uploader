<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class RabbitMqPublisher
{
    public function publish(array $payload): void
    {
        $connection = null;
        $channel = null;

        try {
            $connection = new AMQPStreamConnection(
                config('services.rabbitmq.host'),
                (int) config('services.rabbitmq.port'),
                config('services.rabbitmq.user'),
                config('services.rabbitmq.password')
            );

            $channel = $connection->channel();

            $queue = config('services.rabbitmq.queue');

            $channel->queue_declare(
                $queue,
                false,
                true,
                false,
                false
            );

            $message = new AMQPMessage(
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => 2,
                ]
            );

            $channel->basic_publish($message, '', $queue);
        } catch (Throwable $e) {
            Log::error('RabbitMQ publish failed', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);
        } finally {
            try {
                if ($channel) {
                    $channel->close();
                }
            } catch (Throwable) {
            }

            try {
                if ($connection) {
                    $connection->close();
                }
            } catch (Throwable) {
            }
        }
    }
}
