<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * WebAuth middleware
 *
 * Protects Blade pages by requiring a session token.
 *
 * Expected session keys
 * - api_token: set after successful login/register
 * - user: basic user info (optional convenience)
 *
 * Behavior
 * - If api_token missing, redirect to /login.
 */
class WebAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request Current HTTP request.
     * @param Closure $next Next middleware.
     * @return mixed Redirect response or downstream response.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('api_token')) {
            return redirect('/login');
        }

        return $next($request);
    }
}