<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShareLink;
use Illuminate\Http\Request;

/**
 * DownloadPageController
 *
 * GET /download/{token}
 *
 * Responsibilities:
 * - Resolve token to ShareLink row using ShareLink::resolveToken()
 * - Enforce restricted access rules:
 *   - If restrict_email exists:
 *       - require login
 *       - require logged in user's email == restrict_email
 * - Show page with file name, expiry, remaining downloads
 * - Show message when invalid / expired / revoked / limit reached
 */
class DownloadPageController extends Controller
{
    public function show(Request $request, string $token)
    {
        $link = ShareLink::resolveToken($token);

        if (!$link) {
            return view('download.show', [
                'status' => 'not_found',
                'link' => null,
                'file' => null,
            ]);
        }

        $file = $link->fileItem;

        // Restricted link: require login + email match.
        if ($link->restrict_email) {
            $user = $request->user();
            if (!$user) {
                return redirect()->route('login.form')->with('redirect_after_login', url()->current());
            }

            if (strtolower($user->email) !== strtolower($link->restrict_email)) {
                return view('download.show', [
                    'status' => 'unauthorized',
                    'link' => $link,
                    'file' => $file,
                ]);
            }
        }

        // Link validity checks.
        if ($link->revoked_at) {
            return view('download.show', [
                'status' => 'revoked',
                'link' => $link,
                'file' => $file,
            ]);
        }

        if ($link->expires_at && now()->greaterThan($link->expires_at)) {
            return view('download.show', [
                'status' => 'expired',
                'link' => $link,
                'file' => $file,
            ]);
        }

        if ($link->downloads_count >= $link->max_downloads) {
            return view('download.show', [
                'status' => 'limit',
                'link' => $link,
                'file' => $file,
            ]);
        }

        return view('download.show', [
            'status' => 'ok',
            'link' => $link,
            'file' => $file,
        ]);
    }
}
