<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// P1: Auth & Admin routes
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
if (file_exists(__DIR__.'/admin.php')) {
    require __DIR__.'/admin.php';
}

// P2: List & Task routes
if (file_exists(__DIR__.'/lists.php')) {
    require __DIR__.'/lists.php';
}
if (file_exists(__DIR__.'/tasks.php')) {
    require __DIR__.'/tasks.php';
}

// P3: Collaboration & Monitoring routes
if (file_exists(__DIR__.'/collaboration.php')) {
    require __DIR__.'/collaboration.php';
}
