<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileItem extends Model
{
    protected $fillable = [
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'extension',
        'size',
        'uploaded_at',
        'expires_at',
        'deleted_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
