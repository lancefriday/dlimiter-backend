<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FileItem
 *
 * Represents one uploaded file.
 *
 * Key fields
 * - owner_id: uploader (users.id)
 * - original_name: original filename shown in UI
 * - storage_disk: disk name (example: local)
 * - storage_path: path in storage disk (example: uploads/{userId}/file.pdf)
 * - size_bytes: raw size in bytes
 * - mime_type: file mime type
 * - sha256: optional content hash for dedupe/integrity
 *
 * Relationships
 * - owner(): uploader user
 * - shareLinks(): links created for this file
 */
class FileItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable columns.
     * Keep this aligned with your migration schema.
     */
    protected $fillable = [
        'owner_id',
        'original_name',
        'storage_disk',
        'storage_path',
        'size_bytes',
        'mime_type',
        'sha256',
    ];

    /**
     * Uploader relationship.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Share links relationship.
     */
    public function shareLinks()
    {
        return $this->hasMany(ShareLink::class);
    }
}