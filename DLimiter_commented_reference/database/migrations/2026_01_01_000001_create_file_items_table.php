<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create file_items table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->string('original_name');
            $table->string('storage_path');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('mime_type')->nullable();
            $table->timestamps();

            $table->index('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_items');
    }
};
