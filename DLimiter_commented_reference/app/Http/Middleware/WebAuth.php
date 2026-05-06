<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * WebAuth middleware
 *
 * Purpose:
 * - Protects server-rendered pages that require a logged-in user.
 *
 * How it works:
 * - Uses Laravel's web guard (session-based auth).
 * - If the user is not authenticated, redirect to /login.
 *
 * Why this exists:
 * - It keeps routing readable: Route::middleware(['web.auth'])->group(...)
 * - It avoids repeating auth checks inside every controller method.
 */
class WebAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login.form');
        }

        return $next($request);
    }
}
