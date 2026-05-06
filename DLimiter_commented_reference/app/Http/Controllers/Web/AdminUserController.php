<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * AdminUserController
 *
 * Admin page to list users and toggle is_admin.
 */
class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->orderBy('id')
            ->get();

        return view('admin.users', [
            'users' => $users,
            'user' => $request->user(),
        ]);
    }

    /**
     * Toggle admin flag for a user.
     *
     * Safety:
     * - Prevent removing admin from yourself (optional)
     */
    public function toggleAdmin(Request $request, int $userId)
    {
        $current = $request->user();

        $user = User::query()->findOrFail($userId);

        if ($user->id === $current->id) {
            return redirect()->route('admin.users.index');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return redirect()->route('admin.users.index');
    }
}
