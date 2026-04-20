<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShareLink extends Model
{
    use HasFactory;
    public static function resolveToken(string $token): ?self
        {
            $prefix = self::prefixToken($token);
            $hash = self::hashToken($token);

            return self::query()
                ->where('token_prefix', $prefix)
                ->where('token_hash', $hash)
                ->first();
        }

    protected $fillable = [
        'file_item_id',
        'creator_id',
        'token_prefix',
        'token_hash',
        'is_public',
        'downloader_user_id',
        'max_downloads',
        'downloads_count',
        'expires_at',
        'revoked_at',
        'last_download_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_download_at' => 'datetime',
    ];

    public function fileItem()
    {
        return $this->belongsTo(FileItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function downloaderUser()
    {
        return $this->belongsTo(User::class, 'downloader_user_id');
    }

    public static function makeToken(): string
    {
        // 64-char hex token (URL safe, cryptographically strong)
        return bin2hex(random_bytes(32));
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function prefixToken(string $token): string
    {
        return substr($token, 0, 12);
    }

    public function isRevoked(): bool
    {
        return (bool) $this->revoked_at;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->downloads_count >= $this->max_downloads;
    }

    public function remainingDownloads(): int
    {
        return max(0, $this->max_downloads - $this->downloads_count);
    }


}
