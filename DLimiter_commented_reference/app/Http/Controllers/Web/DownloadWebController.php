<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DownloadEvent;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * DownloadWebController
 *
 * POST /download/{token}
 *
 * This endpoint performs the actual file download.
 * The GET page is informational. The POST is the action.
 *
 * Security:
 * - Repeat the same checks as DownloadPageController (do not trust GET only)
 * - Log download event
 * - Increment downloads_count after successful stream start
 */
class DownloadWebController extends Controller
{
    public function download(Request $request, string $token): StreamedResponse
    {
        $link = ShareLink::resolveToken($token);
        if (!$link) {
            abort(404);
        }

        $file = $link->fileItem;

        // Restricted link checks.
        if ($link->restrict_email) {
            $user = $request->user();
            if (!$user) {
                abort(401);
            }

            if (strtolower($user->email) !== strtolower($link->restrict_email)) {
                abort(403);
            }
        }

        // Validity checks.
        if (!$link->isActive()) {
            // Show a proper page rather than a raw 403.
            return response()->streamDownload(function () {
                echo "Link not active.";
            }, "link-not-active.txt");
        }

        // Record download event (audit).
        DownloadEvent::create([
            'file_item_id' => $file->id,
            'share_link_id' => $link->id,
            'downloader_user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'downloaded_at' => now(),
        ]);

        // Increment count right before sending.
        $link->downloads_count = (int) $link->downloads_count + 1;
        $link->save();

        $downloadName = $file->original_name ?: 'download.bin';

        return Storage::download($file->storage_path, $downloadName);
    }
}
