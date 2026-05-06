<?php

namespace App\Http\Controllers;

use App\Models\ShareLink;
use Illuminate\Http\Request;

/*
 * LinkMetaController.php
 *
 * Purpose:
 * - Part of the DLimiter backend.
 * - This file contains LinkMetaController and related request handlers.
 *
 * Notes:
 * - Comments in this file describe intent and safety checks.
 * - Token values are sensitive. Store and display them carefully.
 */

/**
 * LinkMetaController
 *
 * Role:
 * - Controller layer that accepts an HTTP request, applies validation and authorization,
 *   then calls model or storage operations.
 */
class LinkMetaController extends Controller
{
/**
 * Return metadata about a share link (file name, size, link limits, expiry) without starting a download.
 *
 * @param Request $request
 * @param string $token
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function show(Request $request, string $token)
    {
        // Resolve token to a link row. Tokens are not stored in plaintext.
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

/**
 * Resolve a plaintext token to a ShareLink row using prefix filtering plus constant-time hash comparison.
 *
 * @param string $token
 * @return ?ShareLink
 */
    private function findByToken(string $token): ?ShareLink
    {
        // Quick guard: prefix lookup uses first 12 chars.
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