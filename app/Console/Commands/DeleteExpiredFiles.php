<?php

namespace App\Console\Commands;

use App\Actions\Files\DeleteFileAction;
use App\Models\FileItem;
use Illuminate\Console\Command;

class DeleteExpiredFiles extends Command
{
    protected $signature = 'files:delete-expired';
    protected $description = 'Delete expired files and publish RabbitMQ notification messages';

    public function handle(DeleteFileAction $deleteFileAction): int
    {
        $expiredFiles = FileItem::query()
            ->whereNull('deleted_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredFiles->isEmpty()) {
            $this->info('No expired files found.');

            return self::SUCCESS;
        }

        foreach ($expiredFiles as $fileItem) {
            $deleteFileAction->execute($fileItem, 'expired');
            $this->info("Deleted file #{$fileItem->id} ({$fileItem->original_name})");
        }

        $this->info('Expired files cleanup completed.');

        return self::SUCCESS;
    }
}
