<?php

namespace App\Consumers;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class FileDeletedConsumer
{
    public function handle(array $payload): void
    {
        $this->validatePayload($payload);

        Log::info('File deletion notification should be sent', [
            'to' => $payload['notification_email'],
            'file_id' => $payload['file_id'],
            'file_name' => $payload['original_name'],
            'reason' => $payload['reason'],
            'deleted_at' => $payload['deleted_at'],
        ]);

        // Example:
        // Mail::to($payload['notification_email'])->send(new FileDeletedMail($payload));
    }

    private function validatePayload(array $payload): void
    {
        $required = [
            'event',
            'file_id',
            'original_name',
            'reason',
            'notification_email',
            'deleted_at',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $payload) || $payload[$field] === null || $payload[$field] === '') {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }

        if ($payload['event'] !== 'file_deleted') {
            throw new InvalidArgumentException('Unsupported event type: ' . $payload['event']);
        }
    }
}
