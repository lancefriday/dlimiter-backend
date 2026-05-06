<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DownloadEvent;
use Illuminate\Http\Request;

/**
 * AdminDownloadEventsController
 *
 * Admin audit page to show download events.
 *
 * Shows:
 * - file name
 * - user email (or "public")
 * - share link id
 * - ip
 * - user agent
 * - timestamp
 */
class AdminDownloadEventsController extends Controller
{
    public function index(Request $request)
    {
        $events = DownloadEvent::query()
            ->leftJoin('file_items', 'download_events.file_item_id', '=', 'file_items.id')
            ->leftJoin('users', 'download_events.downloader_user_id', '=', 'users.id')
            ->select([
                'download_events.*',
                'file_items.original_name as file_name',
                'users.email as user_email',
            ])
            ->orderByDesc('download_events.downloaded_at')
            ->paginate(30);

        return view('admin.download_events', [
            'events' => $events,
            'user' => $request->user(),
        ]);
    }
}
