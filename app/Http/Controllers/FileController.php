<?php

namespace App\Http\Controllers;

use App\Models\FileItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
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

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'file' => ['required','file','max:102400'], // 100MB default
        ]);

        $uploaded = $data['file'];

        $disk = config('filesystems.default');
        $uuid = (string) Str::uuid();
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $uploaded->getClientOriginalName());
        $path = "uploads/{$user->id}/{$uuid}_{$safeName}";

        $stream = fopen($uploaded->getRealPath(), 'r');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) fclose($stream);

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
