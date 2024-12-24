<?php
declare(strict_types=1);

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Route;

use Gui\App\Http\Controllers\Api\VersionController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\AuthController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\DashboardController;

use Scarlett\DMDD\GUI\Http\Middleware\Authenticate;

# Web gui authentication
Route::middleware(['web'])->group(function () {
    Route::get('/auth/login', [AuthController::class, 'get'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/auth/logout', [AuthController::class, 'logout'])->name('logout');
});

# Web gui
Route::middleware([Authenticate::class])->group(function () {
    Route::get('/', [DashboardController::class, 'get'])->name('dashboard')->name('home');
});


# Api, version and health check
Route::get('/api/version', [VersionController::class, 'getVersion']);