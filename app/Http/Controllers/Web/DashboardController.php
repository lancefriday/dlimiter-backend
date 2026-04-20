<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FileItem;
use App\Models\ShareLink;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // from your web.auth middleware

        $totalFiles = FileItem::where('owner_id', $user->id)->count();

        $totalLinks = ShareLink::where('creator_id', $user->id)->count();

        $activeLinks = ShareLink::where('creator_id', $user->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->whereColumn('downloads_count', '<', 'max_downloads')
            ->count();

        return view('dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
            ],
            'totalFiles' => $totalFiles,
            'totalLinks' => $totalLinks,
            'activeLinks' => $activeLinks,
        ]);
    }
}