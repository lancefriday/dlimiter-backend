<?php

namespace App\Jobs;

use App\Models\FileItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * DeleteStoredFile
 *
 * Purpose
 * - Background cleanup job for stored uploads.
 *
 * When it runs
 * - Triggered after link revocation, or when you want cleanup logic.
 *
 * What it does
 * - Loads the file (including soft-deleted).
 * - Checks if any active share link still exists.
 * - If none exist, deletes the stored file from the storage disk.
 * - Soft-deletes the FileItem row if it is not already deleted.
 *
 * Active link definition used here
 * - Not revoked
 * - Not expired
 * - downloads_count < max_downloads
 */
class DeleteStoredFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int $fileItemId FileItem primary key to evaluate for cleanup.
     */
    public function __construct(public int $fileItemId) {}

    /**
     * Job entry point.
     *
     * Notes
     * - This is best-effort cleanup.
     * - If the file is missing on disk, Storage::delete returns false and the job still ends safely.
     */
    public function handle(): void
    {
        // Load file even if soft-deleted.
        $file = FileItem::withTrashed()->find($this->fileItemId);
        if (!$file) {
            return;
        }

        // Keep the stored file if any active link still exists.
        $activeLinkExists = $file->shareLinks()
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereColumn('downloads_count', '<', 'max_downloads')
            ->exists();

        if ($activeLinkExists) {
            return;
        }

        // Delete the stored file (best-effort).
        Storage::disk($file->storage_disk)->delete($file->storage_path);

        // Soft-delete DB record if not already soft-deleted.
        if (!$file->trashed()) {
            $file->delete();
        }
    }
}