<?php

namespace App\Http\Controllers;

use App\Models\ShareLink;
use Illuminate\Http\Request;

class LinkMetaController extends Controller
{
    public function show(Request $request, string $token)
    {
        $link = $this->findByToken($token);
        if (!$link) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $file = $link->fileItem;

        $requiresAuth = $link->downloader_user_id !== null;

        return response()->json([
            'ok' => true,
            'file' => [
                'name' => $file->original_name,
                'size_bytes' => $file->size_bytes,
                'mime_type' => $file->mime_type,
            ],
            'link' => [
                'is_public' => $link->is_public,
                'requires_auth' => $requiresAuth,
                'max_downloads' => $link->max_downloads,
                'downloads_count' => $link->downloads_count,
                'remaining' => $link->remainingDownloads(),
                'expires_at' => optional($link->expires_at)->toIso8601String(),
                'revoked_at' => optional($link->revoked_at)->toIso8601String(),
            ],
        ]);
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
