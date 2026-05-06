<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * AuthWebController
 *
 * Handles login, register, logout for Blade pages.
 *
 * Session based auth:
 * - login(): Auth::attempt(), then redirects to /dashboard
 * - logout(): Auth::logout(), then redirects to /
 */
class AuthWebController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Validate inputs, attempt login, then create session.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($data)) {
            return back()
                ->withErrors(['email' => 'Login failed'])
                ->withInput();
        }

        // Session fixation protection: new session id after login.
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Register a new user and login.
     *
     * Rules:
     * - email unique
     * - password min length
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Logout uses POST and CSRF token.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate session + regenerate token to avoid reuse.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
