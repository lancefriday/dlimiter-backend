<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_item_id')->constrained('file_items')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();

            // Token storage: store ONLY a hash, never the raw token.
            $table->string('token_prefix', 12); // first 12 chars of raw token
            $table->string('token_hash', 64)->unique();

            $table->boolean('is_public')->default(true);
            $table->foreignId('downloader_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedInteger('max_downloads')->default(1);
            $table->unsignedInteger('downloads_count')->default(0);
            $table->timestamp('expires_at')->nullable();

            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_download_at')->nullable();
            $table->timestamps();

            $table->index(['token_prefix']);
            $table->index(['file_item_id', 'revoked_at']);
            $table->index(['downloader_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_links');
    }
};
