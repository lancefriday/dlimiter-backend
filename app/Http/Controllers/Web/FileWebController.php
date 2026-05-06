<?php

/**
 * FileWebController.php
 *
 * Web UI controller for file listing, upload, and deletion.
 *
 * Routes:
 *   - GET /files -> index()
 *   - POST /files/upload -> upload()
 *   - POST /files/{fileId}/delete -> delete()
 *
 * Notes:
 *   - Uploads store the file under storage/app/uploads/{userId}/... and create a FileItem record.
 *   - Delete removes file storage, related links, and related download events.
 */


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Web UI controller for file listing, upload, and deletion.
 */
class FileWebController extends Controller
{
/**
 * Render the page listing the relevant records for the current user.
 *
 * @param Request $request
 * @return \Illuminate\View\View|mixed
 */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = FileItem::query()->latest();

        if (!$user->is_admin) {
            $query->where('owner_id', $user->id);
        }

        $files = $query->paginate(20);

        return view('files.index', [
            'files' => $files,
            'share_token' => session('share_token'),
        ]);
    }

/**
 * Validate and store an uploaded file, then create a FileItem record.
 *
 * @param Request $request
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
 */
    public function upload(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'file' => ['required','file','max:51200'], // 50MB
        ]);

        $file = $request->file('file');

        $storedPath = $file->store("uploads/{$user->id}", 'local');

        FileItem::create([
            'owner_id' => $user->id,
            'original_name' => $file->getClientOriginalName(),
            'size_bytes' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'storage_disk' => 'local',
            'storage_path' => $storedPath,
        ]);

        return redirect('/files')->with('ok', 'Uploaded!');
    }

/**
 * Delete a file item and its stored file, and remove related links and events.
 *
 * @param Request $request
 * @param int $fileId
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
 */
    public function delete(Request $request, int $fileId)
    {
    $user = $request->user();
    $file = \App\Models\FileItem::findOrFail($fileId);

    if (!$user->is_admin && $file->owner_id !== $user->id) {
        abort(403);
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($file) {
        // revoke all share links
        \App\Models\ShareLink::where('file_item_id', $file->id)->update([
            'revoked_at' => now(),
        ]);

        // delete stored file
        $disk = $file->storage_disk ?? 'local';
        $path = $file->storage_path;

        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk($disk)->delete($path);
        }

        // delete related rows
        \Illuminate\Support\Facades\DB::table('download_events')->where('file_item_id', $file->id)->delete();
        \App\Models\ShareLink::where('file_item_id', $file->id)->delete();
        $file->delete();
    });

    return redirect('/files')->with('ok', 'File deleted.');
    }
}