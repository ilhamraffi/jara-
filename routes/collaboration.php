<?php

use App\Http\Controllers\CollaborationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.jwt')->group(function () {
    Route::post('/lists/{id}/members', [CollaborationController::class, 'store']);
    Route::delete('/lists/{id}/members/{userId}', [CollaborationController::class, 'destroy']);
    Route::get('/lists/{id}/members', [CollaborationController::class, 'index']);
    Route::get('/lists/{id}/progress', [CollaborationController::class, 'progress']);
    Route::get('/dashboard', [CollaborationController::class, 'dashboard']);
});
