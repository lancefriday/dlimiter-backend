<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileWebController extends Controller
{
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
}