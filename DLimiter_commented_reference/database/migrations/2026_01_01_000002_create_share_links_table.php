<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create share_links table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_item_id');
            $table->unsignedBigInteger('created_by_user_id');

            // Token parts (raw token never stored)
            $table->string('token_prefix', 10);
            $table->string('token_hash', 64);

            // Public or restricted
            $table->boolean('is_public')->default(true);
            $table->string('restrict_email')->nullable();

            // Limits
            $table->unsignedInteger('max_downloads')->default(1);
            $table->unsignedInteger('downloads_count')->default(0);

            // Expiry and revoke
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();

            $table->index(['token_prefix', 'token_hash']);
            $table->index('file_item_id');
            $table->index('created_by_user_id');

            $table->foreign('file_item_id')->references('id')->on('file_items')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_links');
    }
};
