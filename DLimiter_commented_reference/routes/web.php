<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FileWebController;
use App\Http\Controllers\Web\LinkWebController;
use App\Http\Controllers\Web\DownloadPageController;
use App\Http\Controllers\Web\DownloadWebController;
use App\Http\Controllers\Web\AdminUserController;
use App\Http\Controllers\Web\AdminDownloadEventsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| DLimiter uses server-rendered Blade pages.
| Authentication uses the "web" guard (session + cookies).
|
| Public:
| - Landing page
| - Login/Register pages
| - Public download page: /download/{token}
|
| Auth-only:
| - Dashboard / Files / Links
| - Restricted download page uses the same /download/{token} route,
|   but the controller enforces "restrict_email" rules.
|
| Admin-only:
| - Manage users (toggle admin)
| - View download events (audit)
|
*/

Route::get('/', [DashboardController::class, 'home'])->name('home');

/* Auth pages */
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register.form');
Route::post('/register', [AuthWebController::class, 'register'])->name('register.submit');

/*
| Logout uses POST to avoid CSRF and browser prefetch issues.
| If your UI links to /logout directly, convert it into a POST form.
*/
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

/* Download page: token is public route, but controller enforces restrictions when required */
Route::get('/download/{token}', [DownloadPageController::class, 'show'])->name('download.show');
Route::post('/download/{token}', [DownloadWebController::class, 'download'])->name('download.perform');

/* Auth-only area */
Route::middleware(['web', 'web.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::get('/files', [FileWebController::class, 'index'])->name('files.index');
    Route::post('/files/upload', [FileWebController::class, 'upload'])->name('files.upload');
    Route::post('/files/{fileId}/delete', [FileWebController::class, 'delete'])->name('files.delete');
    Route::post('/files/{fileId}/links', [FileWebController::class, 'createLink'])->name('files.links.create');

    Route::get('/links', [LinkWebController::class, 'index'])->name('links.index');
    Route::post('/links/{linkId}/revoke', [LinkWebController::class, 'revoke'])->name('links.revoke');

    /* Admin area */
    Route::middleware(['web.admin'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('/users/{userId}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('admin.users.toggle');

        Route::get('/download-events', [AdminDownloadEventsController::class, 'index'])->name('admin.download_events.index');
    });
});
