<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class LinkWebController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ShareLink::query()
            ->with('fileItem')
            ->latest();

        // non-admin sees only their files' links
        if (!$user->is_admin) {
            $query->whereHas('fileItem', fn($q) => $q->where('owner_id', $user->id));
        }

        $links = $query->paginate(20);

        return view('links.index', compact('links'));
    }

    public function create(Request $request, int $fileId)
    {
        $user = $request->user();
        $fileItem = FileItem::findOrFail($fileId);

        // Only owner/admin can create links
        if (!$user->is_admin && $fileItem->owner_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'max_downloads' => ['required','integer','min:1','max:1000'],
            'expires_in_minutes' => ['required','integer','min:1','max:525600'],
            'is_public' => ['nullable'],
            'downloader_email' => ['nullable','email'],
        ]);

        $max = (int) $data['max_downloads'];
        $expiresAt = now()->addMinutes((int)$data['expires_in_minutes']);

        // Default public if checkbox checked
        $isPublic = $request->has('is_public');

        // If restricted email provided -> force non-public + set downloader_user_id
        $downloaderId = null;
        if (!empty($data['downloader_email'])) {
            $downloader = User::where('email', $data['downloader_email'])->first();
            if (!$downloader) {
                return back()->withErrors(['link' => 'Downloader email not found. Ask them to register first.']);
            }
            $downloaderId = $downloader->id;
            $isPublic = false;
        }

        // Generate token + store hash/prefix
        $token = ShareLink::makeToken();
        $tokenHash = ShareLink::hashToken($token);
        $tokenPrefix = ShareLink::prefixToken($token);

        $link = ShareLink::create([
            'file_item_id' => $fileItem->id,
            'creator_id' => $user->id,
            'token_prefix' => $tokenPrefix,
            'token_hash' => $tokenHash,
            'is_public' => $isPublic,
            'downloader_user_id' => $downloaderId,
            'max_downloads' => $max,
            'downloads_count' => 0,
            'expires_at' => $expiresAt,
        ]);

        // Save plaintext token encrypted (so Links page can display the real URL later)
        if (Schema::hasColumn('share_links', 'token_enc')) {
            $link->token_enc = Crypt::encryptString($token);
            $link->save();
        }

        return redirect('/files')->with('share_token', $token);
    }

    public function revoke(Request $request, int $linkId)
    {
        $user = $request->user();

        $shareLink = ShareLink::with('fileItem')->findOrFail($linkId);
        $file = $shareLink->fileItem;

        // Only owner/admin can revoke
        if (!$user->is_admin && $file->owner_id !== $user->id) {
            abort(403);
        }

        $shareLink->update(['revoked_at' => now()]);

        return back()->with('ok', 'Revoked!');
    }
}