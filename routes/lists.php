<?php

use App\Http\Controllers\ListController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.jwt')->group(function () {
    Route::post('/lists', [ListController::class, 'store']);
    Route::get('/lists', [ListController::class, 'index']);
    Route::get('/lists/{id}', [ListController::class, 'show']);
    Route::put('/lists/{id}', [ListController::class, 'update']);
    Route::delete('/lists/{id}', [ListController::class, 'destroy']);
});
