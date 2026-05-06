<?php

namespace App\Http\Controllers;

use App\Models\FileItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 * FileController.php
 *
 * Purpose:
 * - Part of the DLimiter backend.
 * - This file contains FileController and related request handlers.
 *
 * Notes:
 * - Comments in this file describe intent and safety checks.
 * - Token values are sensitive. Store and display them carefully.
 */

/**
 * FileController
 *
 * Role:
 * - Controller layer that accepts an HTTP request, applies validation and authorization,
 *   then calls model or storage operations.
 */
class FileController extends Controller
{
/**
 * List files visible to the current user. Admin sees all, non-admin sees only owned files.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = FileItem::query()->latest();

        if (!$user->is_admin) {
            $query->where('owner_id', $user->id);
        }

        return response()->json([
            'files' => $query->withCount('shareLinks')->paginate(20),
        ]);
    }

/**
 * Upload a file, store it on disk, record metadata and hash in the database, return created file record.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'file' => ['required','file','max:102400'], // 100MB default
        ]);

        $uploaded = $data['file'];

        $disk = config('filesystems.default');
        $uuid = (string) Str::uuid();
        // Build a filesystem-safe name to avoid path traversal and odd characters.
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $uploaded->getClientOriginalName());
        $path = "uploads/{$user->id}/{$uuid}_{$safeName}";

        $stream = fopen($uploaded->getRealPath(), 'r');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) fclose($stream);

        // Content hash supports integrity checks and duplicate detection.
        $sha256 = hash_file('sha256', $uploaded->getRealPath());

        $file = FileItem::create([
            'owner_id' => $user->id,
            'original_name' => $uploaded->getClientOriginalName(),
            'storage_disk' => $disk,
            'storage_path' => $path,
            'size_bytes' => $uploaded->getSize(),
            'mime_type' => $uploaded->getMimeType(),
            'sha256' => $sha256,
        ]);

        return response()->json(['file' => $file], 201);
    }

/**
 * Delete a file item, revoke associated share links, remove stored object, delete DB row.
 *
 * @param Request $request
 * @param FileItem $fileItem
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function destroy(Request $request, FileItem $fileItem)
    {
        $this->authorize('delete', $fileItem);

        // Revoke all links
        $fileItem->shareLinks()->update(['revoked_at' => now()]);

        // Delete object
        Storage::disk($fileItem->storage_disk)->delete($fileItem->storage_path);

        $fileItem->delete();

        return response()->json(['ok' => true]);
    }
}