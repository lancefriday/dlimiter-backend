<?php

/**
 * LinkWebController.php
 *
 * Handles share-link listing, creation (public/restricted), and revocation for file items in the web UI.
 *
 * Routes:
 *   - GET /links -> index()
 *   - POST /files/{fileId}/links -> create()
 *   - POST /links/{linkId}/revoke -> revoke()
 *
 * Notes:
 *   - Requires session login (web.auth). Non-admin users only operate on their own files/links.
 *   - When creating a link, the raw token is shown once to the creator. The database stores a prefix + hash for verification.
 *   - If share_links.token_enc exists, the raw token is also stored encrypted so the Links UI can display a copyable URL later.
 */


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

/**
 * Handles share-link listing, creation (public/restricted), and revocation for file items in the web UI.
 */
class LinkWebController extends Controller
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

/**
 * Create a new share link for the given file item using the submitted policy settings.
 *
 * @param Request $request
 * @param int $fileId
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
 */
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

/**
 * Revoke an existing share link (sets revoked_at).
 *
 * @param Request $request
 * @param int $linkId
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
 */
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