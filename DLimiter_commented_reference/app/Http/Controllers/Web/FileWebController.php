<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * FileWebController
 *
 * Core features:
 * - Upload a file
 * - List owned files
 * - Delete owned file (and related links)
 * - Create a share link for a file (public or restricted)
 */
class FileWebController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $files = FileItem::query()
            ->where('owner_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return view('files.index', [
            'user' => $user,
            'files' => $files,
        ]);
    }

    /**
     * Upload a file into storage/app/uploads/{userId}/
     * and insert a FileItem row.
     */
    public function upload(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50MB
        ]);

        $uploaded = $data['file'];

        $path = $uploaded->store('uploads/' . $user->id);

        FileItem::create([
            'owner_id' => $user->id,
            'original_name' => $uploaded->getClientOriginalName(),
            'storage_path' => $path,
            'size_bytes' => $uploaded->getSize(),
            'mime_type' => $uploaded->getClientMimeType(),
        ]);

        return redirect()->route('files.index');
    }

    /**
     * Delete a file owned by the user.
     *
     * For safety:
     * - remove DB row
     * - remove physical file
     * - related share links will be deleted via DB constraints or explicit deletion
     */
    public function delete(Request $request, int $fileId)
    {
        $user = $request->user();

        $file = FileItem::query()
            ->where('id', $fileId)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        // Delete related links first to avoid dangling downloads.
        ShareLink::query()
            ->where('file_item_id', $file->id)
            ->delete();

        Storage::delete($file->storage_path);

        $file->delete();

        return redirect()->route('files.index');
    }

    /**
     * Create share link for a file.
     *
     * UI inputs:
     * - max_downloads
     * - expires_in_minutes
     * - is_public checkbox
     * - restrict_email optional (if filled, treat as restricted)
     */
    public function createLink(Request $request, int $fileId)
    {
        $user = $request->user();

        $file = FileItem::query()
            ->where('id', $fileId)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        $data = $request->validate([
            'max_downloads' => ['required', 'integer', 'min:1', 'max:50'],
            'expires_in_minutes' => ['required', 'integer', 'min:1', 'max:10080'], // up to 7 days
            'is_public' => ['nullable'],
            'restrict_email' => ['nullable', 'email'],
        ]);

        $restrictEmail = $data['restrict_email'] ?? null;

        // If restrict_email is filled, force non-public.
        $isPublic = empty($restrictEmail) && !empty($data['is_public']);

        [$link, $rawToken] = ShareLink::issueForFile(
            file: $file,
            creator: $user,
            isPublic: $isPublic,
            restrictEmail: $restrictEmail,
            maxDownloads: (int) $data['max_downloads'],
            expiresInMinutes: (int) $data['expires_in_minutes']
        );

        // Build download page URL for display and copying.
        $downloadUrl = route('download.show', ['token' => $rawToken]);

        return redirect()
            ->route('files.index')
            ->with('created_link_url', $downloadUrl);
    }
}
