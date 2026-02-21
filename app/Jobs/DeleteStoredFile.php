<?php

namespace App\Jobs;

use App\Models\FileItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteStoredFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $fileItemId) {}

    public function handle(): void
    {
        $file = FileItem::withTrashed()->find($this->fileItemId);
        if (!$file) return;

        // If there is any non-revoked, non-expired, non-exhausted link, keep the file.
        $activeLinkExists = $file->shareLinks()
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereColumn('downloads_count', '<', 'max_downloads')
            ->exists();

        if ($activeLinkExists) return;

        // Delete the object (best-effort)
        Storage::disk($file->storage_disk)->delete($file->storage_path);

        // Soft-delete file record if not already
        if (!$file->trashed()) {
            $file->delete();
        }
    }
}
