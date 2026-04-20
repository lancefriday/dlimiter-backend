<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShareLink;
use Illuminate\Http\Request;

class DownloadPageController extends Controller
{
    public function show(Request $request, string $token)
    {
        $link = ShareLink::resolveToken($token);
        if (!$link) abort(404);

        $link->load('fileItem');

        $expired = $link->expires_at && $link->expires_at->isPast();
        $revoked = !is_null($link->revoked_at);
        $limitReached = $link->downloads_count >= $link->max_downloads;

        $blocked = $expired || $revoked || $limitReached;

        // For restricted links: require login to even view the "Download" button
        $loginRequired = !$link->is_public;

        // If specific user restriction is set, only that user can proceed
        $notAllowedUser = false;
        if ($loginRequired) {
            if (!$request->user()) {
                // show page but button will guide to login
                // (or you can redirect immediately; your choice)
            } else {
                if ($link->downloader_user_id && $request->user()->id !== $link->downloader_user_id) {
                    $notAllowedUser = true;
                    $blocked = true;
                }
            }
        }

        $downloadUrl = $link->is_public
            ? "/api/d/{$token}"
            : "/download-auth/{$token}"; // session-based restricted download

        return view('download.show', [
            'token' => $token,
            'link' => $link,
            'file' => $link->fileItem,
            'expired' => $expired,
            'revoked' => $revoked,
            'limitReached' => $limitReached,
            'blocked' => $blocked,
            'loginRequired' => $loginRequired,
            'notAllowedUser' => $notAllowedUser,
            'remaining' => max(0, $link->max_downloads - $link->downloads_count),
            'downloadUrl' => $downloadUrl,
        ]);
    }
}