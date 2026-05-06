<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/*
 * AuthController.php
 *
 * Purpose:
 * - Part of the DLimiter backend.
 * - This file contains AuthController and related request handlers.
 *
 * Notes:
 * - Comments in this file describe intent and safety checks.
 * - Token values are sensitive. Store and display them carefully.
 */

/**
 * AuthController
 *
 * Role:
 * - Controller layer that accepts an HTTP request, applies validation and authorization,
 *   then calls model or storage operations.
 */
class AuthController extends Controller
{
/**
 * Create a user account for API usage and return a Sanctum token.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

/**
 * Authenticate credentials and return a fresh Sanctum token. Optionally removes old tokens.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        // Auth::attempt validates password hash and starts a session context.
        if (!Auth::attempt($data)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Optionally delete old tokens
        // Optional hardening: remove previous tokens to reduce token sprawl.
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

/**
 * Revoke the current access token for the authenticated API user.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }
        return response()->json(['ok' => true]);
    }

/**
 * Return the currently authenticated API user.
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|mixed
 */
    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }
}