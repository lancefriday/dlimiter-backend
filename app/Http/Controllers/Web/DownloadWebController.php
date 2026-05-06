<?php

/**
 * DownloadWebController.php
 *
 * Session-based restricted download endpoint. Enforces login + optional downloader restriction and streams file.
 *
 * Routes:
 *   - GET /download-auth/{token} -> authDownload()
 *
 * Notes:
 *   - Only for restricted (non-public) share links.
 *   - Increments download count and records a download event (downloader_user_id, ip, user_agent, downloaded_at).
 */


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Session-based restricted download endpoint. Enforces login + optional downloader restriction and streams file.
 */
class DownloadWebController extends Controller
{
    // Session-based restricted download
/**
 * Enforce restricted-link rules and stream the stored file to the authenticated user.
 *
 * @param Request $request
 * @param string $token
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
 */
    public function authDownload(Request $request, string $token)
    {
        $user = $request->user();
        if (!$user) return redirect('/login');

        $link = ShareLink::resolveToken($token);
        if (!$link) abort(404);

        // must be restricted (non-public)
        if ($link->is_public) abort(400, 'This link is public. Use the public download route.');

        // enforce specific user restriction if set
        if ($link->downloader_user_id && $user->id !== $link->downloader_user_id) {
            abort(403);
        }

        // standard checks
        if ($link->revoked_at) abort(403, 'Link revoked.');
        if ($link->expires_at && $link->expires_at->isPast()) abort(403, 'Link expired.');
        if ($link->downloads_count >= $link->max_downloads) abort(403, 'Download limit reached.');

        $file = FileItem::findOrFail($link->file_item_id);

        // increment downloads + log event (transaction for safety)
        DB::transaction(function () use ($link, $user, $request, $file) {
            $link->increment('downloads_count');

            DB::table('download_events')->insert([
                'file_item_id' => $file->id,
                'share_link_id' => $link->id,
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string)$request->userAgent(), 0, 255),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $disk = $file->storage_disk ?? 'local';
        $path = $file->storage_path;

        if (!Storage::disk($disk)->exists($path)) {
            abort(404, 'Stored file missing.');
        }

        return Storage::disk($disk)->download($path, $file->original_name);
    }
}