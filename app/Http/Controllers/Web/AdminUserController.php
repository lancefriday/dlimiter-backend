<?php

/**
 * AdminUserController.php
 *
 * Admin-only user management UI: list users and promote/demote admin role.
 *
 * Routes:
 *   - GET /admin/users -> index()
 *   - POST /admin/users/{userId}/toggle-admin -> toggleAdmin()
 *
 * Notes:
 *   - Requires admin (is_admin=1).
 *   - Safety: prevents changing your own role.
 */


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin-only user management UI: list users and promote/demote admin role.
 */
class AdminUserController extends Controller
{
/**
 * Abort with 403 if the current user is not an admin.
 *
 * @param Request $request
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
 */
    private function ensureAdmin(Request $request): void
    {
        $user = $request->user();
        if (!$user || !$user->is_admin) {
            abort(403);
        }
    }

/**
 * Render the page listing the relevant records for the current user.
 *
 * @param Request $request
 * @return \Illuminate\View\View|mixed
 */
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $users = User::query()
            ->select('id', 'name', 'email', 'is_admin', 'created_at')
            ->orderBy('id')
            ->paginate(25);

        return view('admin.users', [
            'users' => $users,
            'me' => $request->user(),
        ]);
    }

/**
 * Toggle a user's admin role.
 *
 * @param Request $request
 * @param int $userId
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|mixed
 */
    public function toggleAdmin(Request $request, int $userId)
    {
        $this->ensureAdmin($request);

        $target = User::findOrFail($userId);

        // Optional safety: prevent demoting yourself
        if ($target->id === $request->user()->id) {
            return back()->withErrors(['admin' => 'You cannot change your own admin role.']);
        }

        $target->is_admin = !$target->is_admin;
        $target->save();

        return back()->with('ok', 'Updated user role.');
    }
}