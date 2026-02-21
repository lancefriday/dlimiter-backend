<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'original_name',
        'storage_disk',
        'storage_path',
        'size_bytes',
        'mime_type',
        'sha256',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shareLinks()
    {
        return $this->hasMany(ShareLink::class);
    }
}
