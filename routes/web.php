<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FileWebController;
use App\Http\Controllers\Web\LinkWebController;

use App\Http\Controllers\Web\DownloadPageController;
use App\Http\Controllers\Web\DownloadWebController;

use App\Http\Controllers\Web\AdminDownloadEventsController;
use App\Http\Controllers\Web\AdminUserController;
// ✅ Home page like the screenshot (if logged in -> dashboard, else -> home view)
Route::get('/', function () {
    if (session()->has('api_token')) {
        return redirect('/dashboard');
    }
    return view('home');
});
Route::view('/landing', 'home');// auth pages
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);
Route::get('/register', [AuthWebController::class, 'showRegister']);
Route::post('/register', [AuthWebController::class, 'register']);
Route::post('/logout', [AuthWebController::class, 'logout']);

// optional placeholders so nav won’t 404
Route::view('/blog', 'static.blog');
Route::view('/contact', 'static.contact');

// public download landing page
Route::get('/download/{token}', [DownloadPageController::class, 'show']);

// protected pages
Route::middleware('web.auth')->group(function () {
    // ✅ This is the route that was missing
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/files', [FileWebController::class, 'index']);
    Route::post('/files/upload', [FileWebController::class, 'upload']);
    Route::post('/files/{fileId}/delete', [FileWebController::class, 'delete']);

    Route::get('/links', [LinkWebController::class, 'index']);
    Route::post('/files/{fileId}/links', [LinkWebController::class, 'create']);
    Route::post('/links/{linkId}/revoke', [LinkWebController::class, 'revoke']);

    // restricted download (session-based)
    Route::get('/download-auth/{token}', [DownloadWebController::class, 'authDownload']);

    // admin
    Route::get('/admin/download-events', [AdminDownloadEventsController::class, 'index']);
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::post('/admin/users/{userId}/toggle-admin', [AdminUserController::class, 'toggleAdmin']);
});