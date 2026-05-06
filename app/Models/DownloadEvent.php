<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * DownloadEvent
 *
 * Audit log row for download activity.
 *
 * Typical fields used in your system:
 * - file_item_id
 * - share_link_id
 * - downloader_user_id (nullable for public downloads)
 * - ip (client IP)
 * - user_agent (browser/device string)
 * - downloaded_at (timestamp)
 */
class DownloadEvent extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'file_item_id',
        'share_link_id',
        'downloader_user_id',
        'ip',
        'user_agent',
        'downloaded_at',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    /**
     * Downloaded file relationship.
     */
    public function fileItem()
    {
        return $this->belongsTo(FileItem::class);
    }

    /**
     * Share link relationship.
     */
    public function shareLink()
    {
        return $this->belongsTo(ShareLink::class);
    }

    /**
     * Downloader relationship (nullable for public).
     */
    public function downloaderUser()
    {
        return $this->belongsTo(User::class, 'downloader_user_id');
    }
}