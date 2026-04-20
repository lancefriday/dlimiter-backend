<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        $user = $request->user();
        if (!$user || !$user->is_admin) {
            abort(403);
        }
    }

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