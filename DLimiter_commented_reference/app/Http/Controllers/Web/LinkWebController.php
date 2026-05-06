<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShareLink;
use Illuminate\Http\Request;

/**
 * LinkWebController
 *
 * Shows links created by the user (or admin sees all, if desired).
 * Allows revocation.
 */
class LinkWebController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ShareLink::query()
            ->with('fileItem')
            ->orderByDesc('id');

        if (!$user->is_admin) {
            $query->where('created_by_user_id', $user->id);
        }

        $links = $query->get();

        return view('links.index', [
            'user' => $user,
            'links' => $links,
        ]);
    }

    /**
     * Revoke a share link.
     *
     * After revocation:
     * - download page still renders but shows "revoked"
     * - actual download action should refuse
     */
    public function revoke(Request $request, int $linkId)
    {
        $user = $request->user();

        $link = ShareLink::query()
            ->with('fileItem')
            ->findOrFail($linkId);

        // Only admin or file owner / link creator may revoke.
        $file = $link->fileItem;
        if (!$user->is_admin && $file->owner_id !== $user->id) {
            abort(403);
        }

        $link->revoked_at = now();
        $link->save();

        return redirect()->route('links.index');
    }
}
