<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteStoredFile;
use App\Models\DownloadEvent;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*
 * DownloadController.php
 *
 * Purpose:
 * - Part of the DLimiter backend.
 * - This file contains DownloadController and related request handlers.
 *
 * Notes:
 * - Comments in this file describe intent and safety checks.
 * - Token values are sensitive. Store and display them carefully.
 */

/**
 * DownloadController
 *
 * Role:
 * - Controller layer that accepts an HTTP request, applies validation and authorization,
 *   then calls model or storage operations.
 */
class DownloadController extends Controller
{




    // Public download endpoint: only works for public links (no downloader restriction)
/**
 * Download endpoint for public links only. Rejects restricted links that require authentication.
 *
 * @param Request $request
 * @param string $token
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function publicDownload(Request $request, string $token)
    {
        $link = $this->findByToken($token);
        if (!$link) return response()->json(['message' => 'Not found'], 404);

        if ($link->downloader_user_id !== null || !$link->is_public) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        return $this->serve($request, $link);
    }


/**
 * Shared download implementation that enforces expiry and download limits, logs the download event, streams the file, and triggers cleanup when limits are reached.
 *
 * @param Request $request
 * @param ShareLink $link
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    private function serve(Request $request, ShareLink $link)
    {
        if ($link->isRevoked()) return response()->json(['message' => 'Link revoked'], 410);
        if ($link->isExpired()) return response()->json(['message' => 'Link expired'], 410);

        $file = $link->fileItem;

        // Enforce limits atomically
        $shouldTriggerDelete = false;
        // Use a transaction + row lock to prevent race conditions on downloads_count.
        DB::transaction(function () use ($request, $link, &$shouldTriggerDelete) {
            $locked = ShareLink::where('id', $link->id)->lockForUpdate()->firstOrFail();

            if ($locked->revoked_at) {
                abort(410, 'Link revoked');
            }

            if ($locked->expires_at && $locked->expires_at->isPast()) {
                abort(410, 'Link expired');
            }

            if ($locked->downloads_count >= $locked->max_downloads) {
                abort(410, 'Download limit reached');
            }

            $locked->downloads_count += 1;
            $locked->last_download_at = now();
            $locked->save();

            // Record an audit event for reporting and admin review.
            DownloadEvent::create([
                'file_item_id' => $locked->file_item_id,
                'share_link_id' => $locked->id,
                'downloader_user_id' => optional($request->user())->id,
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'downloaded_at' => now(),
            ]);

            if ($locked->downloads_count >= $locked->max_downloads) {
                $locked->revoked_at = now();
                $locked->save();
                $shouldTriggerDelete = true;
            }
        });

        if ($shouldTriggerDelete) {
            DeleteStoredFile::dispatch($file->id);
        }

        $disk = $file->storage_disk;
        $path = $file->storage_path;

        if (!Storage::disk($disk)->exists($path)) {
            return response()->json(['message' => 'File not found on storage'], 404);
        }

        $filename = $file->original_name;

        $response = new StreamedResponse(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            if ($stream === false) {
                return;
            }
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        });

        $response->headers->set('Content-Type', $file->mime_type ?? 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.addslashes($filename).'"');

        return $response;
    }

/**
 * Resolve a plaintext token to a ShareLink row by prefix+hash match.
 *
 * @param string $token
 * @return ?ShareLink
 */
    private function findByToken(string $token): ?ShareLink
    {
        if (strlen($token) < 12) return null;

        $prefix = substr($token, 0, 12);
        $hash = ShareLink::hashToken($token);

        $candidates = ShareLink::query()
            ->where('token_prefix', $prefix)
            ->with('fileItem')
            ->get();

        foreach ($candidates as $link) {
            if (hash_equals($link->token_hash, $hash)) {
                return $link;
            }
        }
        return null;
    }
}