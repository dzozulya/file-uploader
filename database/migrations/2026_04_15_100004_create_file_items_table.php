<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_items', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('mime_type');
            $table->string('extension', 10);
            $table->unsignedBigInteger('size');
            $table->timestamp('uploaded_at');
            $table->timestamp('expires_at');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('expires_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_items');
    }
};
