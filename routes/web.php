<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FileWebController;
use App\Http\Controllers\Web\LinkWebController;

Route::get('/', fn() => redirect('/dashboard'));

Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);
Route::get('/register', [AuthWebController::class, 'showRegister']);
Route::post('/register', [AuthWebController::class, 'register']);
Route::post('/logout', [AuthWebController::class, 'logout']);

Route::middleware('web.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/files', [FileWebController::class, 'index']);
    Route::post('/files/upload', [FileWebController::class, 'upload']);

    Route::get('/links', [LinkWebController::class, 'index']);
    Route::post('/files/{fileId}/links', [LinkWebController::class, 'create']);
    Route::post('/links/{linkId}/revoke', [LinkWebController::class, 'revoke']);
});