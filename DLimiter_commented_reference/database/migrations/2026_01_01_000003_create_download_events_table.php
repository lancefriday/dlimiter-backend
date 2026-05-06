<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create download_events table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_item_id');
            $table->unsignedBigInteger('share_link_id');

            // Nullable for public downloads
            $table->unsignedBigInteger('downloader_user_id')->nullable();

            // Optional tracking info
            $table->string('ip', 45)->nullable();     // IPv4 or IPv6
            $table->text('user_agent')->nullable();

            $table->timestamp('downloaded_at')->useCurrent();

            $table->timestamps();

            $table->index('file_item_id');
            $table->index('share_link_id');
            $table->index('downloader_user_id');

            $table->foreign('file_item_id')->references('id')->on('file_items')->cascadeOnDelete();
            $table->foreign('share_link_id')->references('id')->on('share_links')->cascadeOnDelete();
            $table->foreign('downloader_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_events');
    }
};
