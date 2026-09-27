<?php

use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\WebViewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Collaboration & Monitoring Routes (P3 - FR-C1 to FR-C6)
|--------------------------------------------------------------------------
*/

// FR-C1: Owner tambah member ke list
Route::post('/lists/{id}/members', [CollaborationController::class, 'addMember'])->name('lists.members.add');

// FR-C2: Owner hapus member dari list
Route::delete('/lists/{id}/members/{user_id}', [CollaborationController::class, 'removeMember'])->name('lists.members.remove');

// FR-C3: Member lihat daftar member
Route::get('/lists/{id}/members', [CollaborationController::class, 'getMembers'])->name('lists.members.index');

// FR-C4: Monitoring progres list (% selesai, status breakdown, upcoming deadlines)
Route::get('/lists/{id}/progress', [CollaborationController::class, 'getProgress'])->name('lists.progress');

// FR-C5: Dashboard ringkasan progres per daftar (Web & JSON)
Route::get('/dashboard', function (Request $request) {
    if (! $request->wantsJson() && ! $request->is('api/*')) {
        return app(WebViewController::class)->dashboard($request);
    }

    return app(CollaborationController::class)->getDashboard($request);
})->name('dashboard');

// FR-C6: Notifikasi deadline tugas & perubahan
Route::get('/notifications', [CollaborationController::class, 'getNotifications'])->name('notifications.index');
