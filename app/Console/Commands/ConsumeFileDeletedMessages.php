<?php

namespace App\Console\Commands;

use App\Consumers\FileDeletedConsumer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class ConsumeFileDeletedMessages extends Command
{
    protected $signature = 'rabbitmq:consume-file-deleted';
    protected $description = 'Consume file deletion events from RabbitMQ';

    public function handle(FileDeletedConsumer $consumer): int
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

            $channel->basic_qos(null, 1, null);

            $this->info("Waiting for messages in queue [{$queue}]...");

            $callback = function (AMQPMessage $message) use ($consumer) {
                try {
                    $payload = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);

                    $consumer->handle($payload);

                    $message->ack();

                    $this->info('Message processed successfully.');
                } catch (Throwable $e) {
                    Log::error('Failed to process RabbitMQ message', [
                        'error' => $e->getMessage(),
                        'body' => $message->getBody(),
                    ]);

                    $message->nack(false, false);

                    $this->error('Message processing failed.');
                }
            };

            $channel->basic_consume(
                $queue,
                '',
                false,
                false,
                false,
                false,
                $callback
            );

            while ($channel->is_consuming()) {
                $channel->wait();
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('RabbitMQ consumer crashed', [
                'error' => $e->getMessage(),
            ]);

            $this->error($e->getMessage());

            return self::FAILURE;
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
