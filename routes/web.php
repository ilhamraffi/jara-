<?php

use App\Http\Controllers\WebViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web UI Routes (JARA - P3 Frontend Implementation)
|--------------------------------------------------------------------------
*/

// Home & Dashboard
Route::get('/', [WebViewController::class, 'dashboard'])->name('home');

// Project Lists & Tasks UI
Route::get('/lists/{id}', [WebViewController::class, 'listDetail'])->name('lists.show');
Route::post('/lists', [WebViewController::class, 'storeList'])->name('lists.store');
Route::post('/lists/{id}/tasks', [WebViewController::class, 'storeTask'])->name('lists.tasks.store');
Route::post('/tasks/{id}/toggle', [WebViewController::class, 'toggleTask'])->name('tasks.toggle');

// User profile & fast switch for collaboration test
Route::get('/profile', [WebViewController::class, 'profile'])->name('profile');
Route::post('/profile', [WebViewController::class, 'updateProfile'])->name('profile.update');
Route::get('/switch-user/{id}', [WebViewController::class, 'switchUser'])->name('switch.user');

// Authentication UI
Route::get('/login', [WebViewController::class, 'loginView'])->name('login');
Route::post('/login', [WebViewController::class, 'doLogin'])->name('login.post');
Route::post('/logout', [WebViewController::class, 'logout'])->name('logout');

// Collaboration & Monitoring Direct API endpoints (FR-C1 to FR-C6)
require __DIR__.'/collaboration.php';
