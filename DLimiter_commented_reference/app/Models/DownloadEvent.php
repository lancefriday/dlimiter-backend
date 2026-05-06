<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DownloadEvent model
 *
 * Tracks downloads for audit logs.
 *
 * Typical fields:
 * - id
 * - file_item_id
 * - share_link_id
 * - downloader_user_id (nullable for public anonymous downloads)
 * - ip (visitor IP, nullable)
 * - user_agent (visitor UA string, nullable)
 * - downloaded_at (timestamp when download succeeded)
 */
class DownloadEvent extends Model
{
    protected $table = 'download_events';

    public $timestamps = true;

    protected $fillable = [
        'file_item_id',
        'share_link_id',
        'downloader_user_id',
        'ip',
        'user_agent',
        'downloaded_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function fileItem(): BelongsTo
    {
        return $this->belongsTo(FileItem::class, 'file_item_id');
    }

    public function shareLink(): BelongsTo
    {
        return $this->belongsTo(ShareLink::class, 'share_link_id');
    }

    public function downloader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'downloader_user_id');
    }
}
