<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteStoredFile;
use App\Models\FileItem;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
 * ShareLinkController.php
 *
 * Purpose:
 * - Part of the DLimiter backend.
 * - This file contains ShareLinkController and related request handlers.
 *
 * Notes:
 * - Comments in this file describe intent and safety checks.
 * - Token values are sensitive. Store and display them carefully.
 */

/**
 * ShareLinkController
 *
 * Role:
 * - Controller layer that accepts an HTTP request, applies validation and authorization,
 *   then calls model or storage operations.
 */
class ShareLinkController extends Controller
{
/**
 * List share links visible to the current user. Admin sees all, non-admin sees only links for owned files.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ShareLink::query()
            ->with(['fileItem:id,owner_id,original_name,size_bytes', 'downloaderUser:id,name,email'])
            ->latest();

        if (!$user->is_admin) {
            $query->whereHas('fileItem', fn($q) => $q->where('owner_id', $user->id));
        }

        return response()->json([
            'links' => $query->paginate(20),
        ]);
    }

/**
 * Create a new share link for a file item, enforce policy, compute token hash/prefix, return plaintext token once.
 *
 * @param Request $request
 * @param FileItem $fileItem
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function store(Request $request, FileItem $fileItem)
    {
        // Policy check: only file owner or admin should create links.
        $this->authorize('update', $fileItem);

        $data = $request->validate([
            'max_downloads' => ['nullable','integer','min:1','max:1000'],
            'expires_in_minutes' => ['nullable','integer','min:1','max:525600'], // up to 1 year
            'downloader_user_id' => ['nullable','integer','exists:users,id'],
            'is_public' => ['nullable','boolean'],
        ]);

        $max = $data['max_downloads'] ?? 1;
        $expiresAt = isset($data['expires_in_minutes']) ? now()->addMinutes($data['expires_in_minutes']) : now()->addHours(1);

        $isPublic = $data['is_public'] ?? true;
        $downloaderId = $data['downloader_user_id'] ?? null;
        if ($downloaderId !== null) {
            $isPublic = false;
        }

        // Generate plaintext token once. Store only hash+prefix in DB.
        $token = ShareLink::makeToken();
        $tokenHash = ShareLink::hashToken($token);
        $tokenPrefix = ShareLink::prefixToken($token);

        $link = ShareLink::create([
            'file_item_id' => $fileItem->id,
            'creator_id' => $request->user()->id,
            'token_prefix' => $tokenPrefix,
            'token_hash' => $tokenHash,
            'is_public' => $isPublic,
            'downloader_user_id' => $downloaderId,
            'max_downloads' => $max,
            'downloads_count' => 0,
            'expires_at' => $expiresAt,
        ]);

        $frontend = rtrim(env('FRONTEND_URL', ''), '/');
        $downloadPage = $frontend ? "{$frontend}/download/{$token}" : null;

        return response()->json([
            'link' => $link,
            'token' => $token,
            'download_page_url' => $downloadPage,
        ], 201);
    }

/**
 * Revoke a share link and trigger cleanup for stored file if no active links remain.
 *
 * @param Request $request
 * @param ShareLink $shareLink
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function revoke(Request $request, ShareLink $shareLink)
    {
        $file = $shareLink->fileItem;
        $this->authorize('update', $file);

        $shareLink->update(['revoked_at' => now()]);

        // If no more active links, delete the stored file
        DeleteStoredFile::dispatch($file->id);

        return response()->json(['ok' => true]);
    }
}