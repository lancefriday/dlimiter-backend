<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use App\Models\ShareLink;
use Illuminate\Http\Request;

/**
 * DashboardController
 *
 * home():
 * - Landing page.
 * - Shows login/register buttons for guests.
 * - Shows a quick-open box for authenticated users.
 *
 * dashboard():
 * - Main workspace summary: counts and quick guide.
 */
class DashboardController extends Controller
{
    public function home(Request $request)
    {
        $user = $request->user();

        return view('home', [
            'user' => $user,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Total files owned by this user.
        $totalFiles = FileItem::query()
            ->where('owner_id', $user->id)
            ->count();

        // Total links created by this user.
        $totalLinks = ShareLink::query()
            ->where('created_by_user_id', $user->id)
            ->count();

        // Active links created by this user:
        // - not revoked
        // - not expired
        // - downloads remaining
        $activeLinks = ShareLink::query()
            ->where('created_by_user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->whereColumn('downloads_count', '<', 'max_downloads')
            ->count();

        return view('dashboard', [
            'user' => $user,
            'totalFiles' => $totalFiles,
            'totalLinks' => $totalLinks,
            'activeLinks' => $activeLinks,
        ]);
    }
}
