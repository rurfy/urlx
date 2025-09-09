<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\QrController;
use App\Http\Controllers\Api\LinkExportController;

Route::post('/links', [LinkController::class, 'store'])
    ->middleware('throttle:60,1');

Route::get('/qr/{slug}', [QrController::class, 'show']);

Route::get('/links', [LinkController::class, 'index']);

Route::get('/links/{slug}/stats', [LinkController::class, 'stats']);

// Debug route
Route::get('/ping', fn () => 'pong');

Route::delete('/links/{slug}', [LinkController::class, 'destroy']);

Route::get('/links/{slug}/export', [LinkExportController::class, 'export']);