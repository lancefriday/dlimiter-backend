<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadEvent extends Model
{
    use HasFactory;

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

    public function fileItem()
    {
        return $this->belongsTo(FileItem::class);
    }

    public function shareLink()
    {
        return $this->belongsTo(ShareLink::class);
    }

    public function downloaderUser()
    {
        return $this->belongsTo(User::class, 'downloader_user_id');
    }
}
