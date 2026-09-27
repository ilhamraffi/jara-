<?php

use Illuminate\Support\Facades\Route;

$loadModules = function () {
    require __DIR__.'/auth.php';
    require __DIR__.'/admin.php';

    if (file_exists(__DIR__.'/lists.php')) {
        require __DIR__.'/lists.php';
    }

    if (file_exists(__DIR__.'/tasks.php')) {
        require __DIR__.'/tasks.php';
    }

    if (file_exists(__DIR__.'/collaboration.php')) {
        require __DIR__.'/collaboration.php';
    }
};

$loadModules();

Route::prefix('api')->group(function () use ($loadModules) {
    $loadModules();
});
