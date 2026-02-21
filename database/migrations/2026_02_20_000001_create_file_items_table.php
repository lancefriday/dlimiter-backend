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
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('storage_disk')->default('local_private');
            $table->string('storage_path');
            $table->unsignedBigInteger('size_bytes');
            $table->string('mime_type')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_items');
    }
};
