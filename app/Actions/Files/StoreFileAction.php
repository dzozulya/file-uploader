<?php

namespace App\Actions\Files;

use App\Models\FileItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreFileAction
{
    public function execute(UploadedFile $uploadedFile): FileItem
    {
        return DB::transaction(function () use ($uploadedFile) {
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            $storedName = Str::uuid() . '.' . $extension;
            $path = $uploadedFile->storeAs('uploads', $storedName, 'public');

            $now = Carbon::now();

            return FileItem::create([
                'original_name' => $uploadedFile->getClientOriginalName(),
                'stored_name' => $storedName,
                'path' => $path,
                'mime_type' => $uploadedFile->getMimeType() ?: $uploadedFile->getClientMimeType(),
                'extension' => $extension,
                'size' => $uploadedFile->getSize(),
                'uploaded_at' => $now,
                'expires_at' => $now->copy()->addHours(24),
                'deleted_at' => null,
            ]);
        });
    }
}
