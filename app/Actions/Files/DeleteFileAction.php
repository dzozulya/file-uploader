<?php

namespace App\Actions\Files;

use App\Models\FileItem;
use App\Services\RabbitMqPublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteFileAction
{
    public function __construct(
        private readonly RabbitMqPublisher $rabbitMqPublisher
    ) {
    }

    public function execute(FileItem $fileItem, string $reason = 'manual'): void
    {
        if ($fileItem->isDeleted()) {
            return;
        }

        DB::transaction(function () use ($fileItem, $reason) {
            if (Storage::disk('public')->exists($fileItem->path)) {
                Storage::disk('public')->delete($fileItem->path);
            }

            $deletedAt = now();

            $fileItem->update([
                'deleted_at' => $deletedAt,
            ]);

            $this->rabbitMqPublisher->publish([
                'event' => 'file_deleted',
                'file_id' => $fileItem->id,
                'original_name' => $fileItem->original_name,
                'stored_name' => $fileItem->stored_name,
                'path' => $fileItem->path,
                'mime_type' => $fileItem->mime_type,
                'extension' => $fileItem->extension,
                'size' => $fileItem->size,
                'reason' => $reason,
                'notification_email' => config('services.notifications.email'),
                'uploaded_at' => optional($fileItem->uploaded_at)?->toDateTimeString(),
                'expires_at' => optional($fileItem->expires_at)?->toDateTimeString(),
                'deleted_at' => $deletedAt->toDateTimeString(),
            ]);
        });
    }
}
