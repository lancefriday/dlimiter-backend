<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('api_token')) {
            return redirect('/login');
        }
        return $next($request);
    }
}