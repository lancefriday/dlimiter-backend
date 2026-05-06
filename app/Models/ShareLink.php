<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ShareLink
 *
 * Represents a shareable download link with controls:
 * - max_downloads: download cap
 * - expires_at: expiry time
 * - is_public: public link vs restricted
 * - downloader_user_id: optional specific allowed user
 * - revoked_at: manual revocation timestamp
 *
 * Token storage strategy
 * - The plaintext token is shown only once at creation time.
 * - DB stores token_prefix + token_hash so the token is not stored in plaintext.
 *
 * Resolution strategy
 * - Given a plaintext token, compute prefix + hash and find the row.
 */
class ShareLink extends Model
{
    use HasFactory;

    /**
     * Resolve a plaintext token to a ShareLink row using prefix + hash match.
     *
     * @param string $token Plaintext token from URL
     * @return self|null Matching ShareLink or null when not found
     */
    public static function resolveToken(string $token): ?self
    {
        $prefix = self::prefixToken($token);
        $hash = self::hashToken($token);

        return self::query()
            ->where('token_prefix', $prefix)
            ->where('token_hash', $hash)
            ->first();
    }

    /**
     * Columns allowed for mass assignment.
     */
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

    /**
     * Type casting for convenience in code and views.
     */
    protected $casts = [
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_download_at' => 'datetime',
    ];

    /**
     * FileItem relationship.
     */
    public function fileItem()
    {
        return $this->belongsTo(FileItem::class);
    }

    /**
     * Creator relationship (user who created the share link).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Downloader relationship (specific allowed downloader for restricted links).
     */
    public function downloaderUser()
    {
        return $this->belongsTo(User::class, 'downloader_user_id');
    }

    /**
     * Create a cryptographically strong URL-safe token.
     *
     * Output format
     * - 64 hex chars (32 random bytes)
     */
    public static function makeToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Hash token for storage lookup.
     */
    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Prefix used to speed up lookup.
     */
    public static function prefixToken(string $token): string
    {
        return substr($token, 0, 12);
    }

    /**
     * True when revoked_at is set.
     */
    public function isRevoked(): bool
    {
        return (bool) $this->revoked_at;
    }

    /**
     * True when expires_at exists and is already past.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * True when downloads_count reached max_downloads.
     */
    public function isExhausted(): bool
    {
        return $this->downloads_count >= $this->max_downloads;
    }

    /**
     * Remaining downloads, never below zero.
     */
    public function remainingDownloads(): int
    {
        return max(0, $this->max_downloads - $this->downloads_count);
    }
}