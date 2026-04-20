<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

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