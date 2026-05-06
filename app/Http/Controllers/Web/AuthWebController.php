<?php

/**
 * AuthWebController.php
 *
 * Web authentication controller for login, registration, and logout (session-based).
 *
 * Routes:
 *   - GET /login -> showLogin()
 *   - POST /login -> login()
 *   - GET /register -> showRegister()
 *   - POST /register -> register()
 *   - GET or POST /logout -> logout()
 *
 * Notes:
 *   - Uses Laravel Auth::attempt for login and Hash::make for registration.
 *   - On login/register, creates a Sanctum token and stores it in session for later use (optional).
 */


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Web authentication controller for login, registration, and logout (session-based).
 */
class AuthWebController extends Controller
{
/**
 * Show the login form page.
 * @return \Illuminate\View\View|mixed
 */
    public function showLogin()
    {
        return view('auth.login');
    }

/**
 * Show the registration form page.
 * @return \Illuminate\View\View|mixed
 */
    public function showRegister()
    {
        return view('auth.register');
    }

/**
 * Authenticate a user using email/password and start a session.
 *
 * @param Request $request
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
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
 * Create a new user account, sign them in, and start a session.
 *
 * @param Request $request
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
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
 * Log out the current user and clear session state.
 *
 * @param Request $request
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
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