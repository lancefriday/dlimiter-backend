<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDownloadEventsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->is_admin) abort(403);

        $events = DB::table('download_events')
            ->leftJoin('file_items', 'download_events.file_item_id', '=', 'file_items.id')
            ->leftJoin('users', 'download_events.downloader_user_id', '=', 'users.id')
            ->select(
                'download_events.id',
                'file_items.original_name as file_name',
                'users.email as user_email',
                'download_events.share_link_id',
                'download_events.ip as ip_address',
                'download_events.user_agent',
                'download_events.downloaded_at'
            )
            ->orderByDesc('download_events.downloaded_at')
            ->paginate(30);

        return view('admin.download_events', ['events' => $events]);
    }
}