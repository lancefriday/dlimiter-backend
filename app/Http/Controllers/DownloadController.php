<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteStoredFile;
use App\Models\DownloadEvent;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    // Public download endpoint: only works for public links (no downloader restriction)
    public function publicDownload(Request $request, string $token)
    {
        $link = $this->findByToken($token);
        if (!$link) return response()->json(['message' => 'Not found'], 404);

        if ($link->downloader_user_id !== null || !$link->is_public) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        return $this->serve($request, $link);
    }

    // Auth-only endpoint: supports restricted links
    public function authDownload(Request $request, string $token)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $link = $this->findByToken($token);
        if (!$link) return response()->json(['message' => 'Not found'], 404);

        if ($link->downloader_user_id !== null && $link->downloader_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $this->serve($request, $link);
    }

    private function serve(Request $request, ShareLink $link)
    {
        if ($link->isRevoked()) return response()->json(['message' => 'Link revoked'], 410);
        if ($link->isExpired()) return response()->json(['message' => 'Link expired'], 410);

        $file = $link->fileItem;

        // Enforce limits atomically
        $shouldTriggerDelete = false;
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
