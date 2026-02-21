<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\DeleteStoredFile;
use App\Models\FileItem;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinkWebController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ShareLink::query()
            ->with(['fileItem:id,owner_id,original_name,size_bytes', 'downloaderUser:id,name,email'])
            ->latest();

        if (!$user->is_admin) {
            $query->whereHas('fileItem', fn($q) => $q->where('owner_id', $user->id));
        }

        $links = $query->paginate(20);

        return view('links.index', [
            'links' => $links,
        ]);
    }

    public function create(Request $request, int $fileId)
    {
        $user = $request->user();
        $fileItem = FileItem::findOrFail($fileId);

        // simple ownership check (no policies needed yet)
        if (!$user->is_admin && $fileItem->owner_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'max_downloads' => ['required','integer','min:1','max:1000'],
            'expires_in_minutes' => ['required','integer','min:1','max:525600'],
            'is_public' => ['nullable'],
        ]);

        $max = (int) $data['max_downloads'];
        $expiresAt = now()->addMinutes((int)$data['expires_in_minutes']);
        $isPublic = $request->has('is_public');

        $token = ShareLink::makeToken();
        $tokenHash = ShareLink::hashToken($token);
        $tokenPrefix = ShareLink::prefixToken($token);

        $link = ShareLink::create([
            'file_item_id' => $fileItem->id,
            'creator_id' => $user->id,
            'token_prefix' => $tokenPrefix,
            'token_hash' => $tokenHash,
            'is_public' => $isPublic,
            'downloader_user_id' => null,
            'max_downloads' => $max,
            'downloads_count' => 0,
            'expires_at' => $expiresAt,
        ]);

        return redirect('/files')->with('share_token', $token);
    }

    public function revoke(Request $request, int $linkId)
    {
        $user = $request->user();
        $shareLink = ShareLink::with('fileItem')->findOrFail($linkId);
        $file = $shareLink->fileItem;

        if (!$user->is_admin && $file->owner_id !== $user->id) {
            abort(403);
        }

        $shareLink->update(['revoked_at' => now()]);

        DeleteStoredFile::dispatch($file->id);

        return back()->with('ok', 'Revoked!');
    }
}