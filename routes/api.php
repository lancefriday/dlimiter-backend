<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\LinkMetaController;
use App\Http\Controllers\ShareLinkController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/links/{token}/meta', [LinkMetaController::class, 'show'])
    ->middleware('throttle:' . env('DOWNLOAD_THROTTLE_PER_MINUTE', 30) . ',1');

Route::get('/d/{token}', [DownloadController::class, 'publicDownload'])
    ->middleware('throttle:' . env('DOWNLOAD_THROTTLE_PER_MINUTE', 30) . ',1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/files', [FileController::class, 'index']);
    Route::post('/files', [FileController::class, 'store']);
    Route::delete('/files/{fileItem}', [FileController::class, 'destroy']);

    Route::get('/links', [ShareLinkController::class, 'index']);
    Route::post('/files/{fileItem}/links', [ShareLinkController::class, 'store']);
    Route::post('/links/{shareLink}/revoke', [ShareLinkController::class, 'revoke']);

    Route::get('/d-auth/{token}', [DownloadController::class, 'authDownload'])
        ->middleware('throttle:' . env('DOWNLOAD_THROTTLE_PER_MINUTE', 30) . ',1');
});
