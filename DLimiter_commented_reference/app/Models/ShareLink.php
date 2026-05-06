<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

/**
 * ShareLink model
 *
 * One row represents a shareable download link for a single file.
 *
 * Security approach:
 * - Store only a token hash in DB, not the raw token.
 * - The raw token is shown to the creator once.
 *
 * Token storage pattern:
 * - token_prefix: first N chars of token, used to narrow lookup.
 * - token_hash: sha256(token), used to verify exact match.
 *
 * Link types:
 * - Public link: visitor may open download page without login
 * - Restricted link: visitor must login, and user email must match restrict_email
 *
 * Expiry and limits:
 * - expires_at: link becomes invalid after this timestamp
 * - max_downloads: hard cap
 * - downloads_count: increments per successful download
 *
 * Revoke:
 * - revoked_at: set when owner revokes link, then link becomes invalid
 */
class ShareLink extends Model
{
    protected $table = 'share_links';

    protected $fillable = [
        'file_item_id',
        'created_by_user_id',
        'token_prefix',
        'token_hash',
        'is_public',
        'restrict_email',
        'max_downloads',
        'downloads_count',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'max_downloads' => 'integer',
        'downloads_count' => 'integer',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function fileItem(): BelongsTo
    {
        return $this->belongsTo(FileItem::class, 'file_item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Create a new link and return [ShareLink $link, string $rawToken].
     * The raw token is only returned once and should be displayed to the user.
     */
    public static function issueForFile(
        FileItem $file,
        User $creator,
        bool $isPublic,
        ?string $restrictEmail,
        int $maxDownloads,
        int $expiresInMinutes
    ): array {
        $rawToken = Str::random(64);

        $prefix = substr($rawToken, 0, 10);
        $hash = hash('sha256', $rawToken);

        $expiresAt = Carbon::now()->addMinutes(max(1, $expiresInMinutes));

        $link = self::create([
            'file_item_id' => $file->id,
            'created_by_user_id' => $creator->id,
            'token_prefix' => $prefix,
            'token_hash' => $hash,
            'is_public' => $isPublic,
            'restrict_email' => $restrictEmail ? strtolower(trim($restrictEmail)) : null,
            'max_downloads' => max(1, $maxDownloads),
            'downloads_count' => 0,
            'expires_at' => $expiresAt,
        ]);

        return [$link, $rawToken];
    }

    /**
     * Resolve a token string into a ShareLink row.
     *
     * This method:
     * - reads prefix + hash
     * - finds by (token_prefix, token_hash)
     *
     * Returns null when not found.
     */
    public static function resolveToken(string $token): ?self
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $prefix = substr($token, 0, 10);
        $hash = hash('sha256', $token);

        return self::query()
            ->where('token_prefix', $prefix)
            ->where('token_hash', $hash)
            ->first();
    }

    /**
     * True when link is still valid for downloading.
     */
    public function isActive(): bool
    {
        if ($this->revoked_at) {
            return false;
        }
        if ($this->expires_at && now()->greaterThan($this->expires_at)) {
            return false;
        }
        if ($this->downloads_count >= $this->max_downloads) {
            return false;
        }
        return true;
    }

    /**
     * Remaining download count.
     */
    public function remainingDownloads(): int
    {
        return max(0, (int) $this->max_downloads - (int) $this->downloads_count);
    }

    /**
     * For UI labeling: "Public" or "Restricted".
     */
    public function typeLabel(): string
    {
        return $this->restrict_email ? 'Restricted' : 'Public';
    }
}
