<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/*
 * AuthWebController.php
 *
 * Purpose:
 * - Part of the DLimiter backend.
 * - This file contains AuthWebController and related request handlers.
 *
 * Notes:
 * - Comments in this file describe intent and safety checks.
 * - Token values are sensitive. Store and display them carefully.
 */

/**
 * AuthWebController
 *
 * Role:
 * - Controller layer that accepts an HTTP request, applies validation and authorization,
 *   then calls model or storage operations.
 */
class AuthWebController extends Controller
{
/**
 * Render the login page for the web UI.
 * @return mixed
 */
    public function showLogin()
    {
        return view('auth.login');
    }

/**
 * Render the registration page for the web UI.
 * @return mixed
 */
    public function showRegister()
    {
        return view('auth.register');
    }

/**
 * Authenticate via web session, create a Sanctum token for internal use, store token and user in session, redirect to dashboard.
 *
 * @param Request $request
 * @return mixed
 */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Create a Sanctum token so the UI can call protected API routes if needed.
        $token = $user->createToken('web')->plainTextToken;

        $request->session()->put('api_token', $token);
        $request->session()->put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => (bool)($user->is_admin ?? false),
        ]);

        return redirect('/dashboard');
    }

/**
 * Create a new user, log them in, create a Sanctum token, store token and user in session, redirect to dashboard.
 *
 * @param Request $request
 * @return mixed
 */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','min:8','confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => 0,
        ]);

        Auth::login($user);

        // Create a Sanctum token so the UI can call protected API routes if needed.
        $token = $user->createToken('web')->plainTextToken;

        $request->session()->put('api_token', $token);
        $request->session()->put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => false,
        ]);

        return redirect('/dashboard');
    }

/**
 * Log out the current web user, revoke tokens, clear session, then redirect to landing/login.
 *
 * @param Request $request
 * @return mixed
 */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}