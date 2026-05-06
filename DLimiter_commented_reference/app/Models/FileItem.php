<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FileItem model
 *
 * One row represents one uploaded file.
 *
 * Fields (typical):
 * - id
 * - owner_id (FK -> users.id)
 * - original_name (filename shown to user)
 * - storage_path (path under storage/app)
 * - size_bytes (raw size in bytes)
 * - mime_type
 * - created_at / updated_at
 *
 * Relationships:
 * - owner(): the user who uploaded the file
 * - shareLinks(): generated links for this file
 */
class FileItem extends Model
{
    protected $table = 'file_items';

    protected $fillable = [
        'owner_id',
        'original_name',
        'storage_path',
        'size_bytes',
        'mime_type',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shareLinks(): HasMany
    {
        return $this->hasMany(ShareLink::class, 'file_item_id');
    }

    /**
     * Human readable file size for UI: KB / MB / GB.
     */
    public function sizeHuman(): string
    {
        $bytes = (int) ($this->size_bytes ?? 0);
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        $kb = $bytes / 1024;
        if ($kb < 1024) {
            return number_format($kb, 1) . ' KB';
        }
        $mb = $kb / 1024;
        if ($mb < 1024) {
            return number_format($mb, 1) . ' MB';
        }
        $gb = $mb / 1024;
        return number_format($gb, 2) . ' GB';
    }
}
