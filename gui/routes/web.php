<?php
declare(strict_types=1);

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Route;

use Gui\App\Http\Controllers\Api\VersionController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\AuthController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\DashboardController;

use Gui\App\Http\Controllers\Api\SystemUserController;
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

# CRUD API for System Users
Route::prefix('api/system')->middleware(['api'])->group(function () {
    Route::get('/users', [SystemUserController::class, 'index'])->name('api.system.users.index');
    Route::post('/users', [SystemUserController::class, 'store'])->name('api.system.users.store');
    Route::get('/users/{user_id}', [SystemUserController::class, 'show'])->name('api.system.users.show');
    Route::put('/users/{user_id}', [SystemUserController::class, 'update'])->name('api.system.users.update');
    Route::delete('/users/{user_id}', [SystemUserController::class, 'destroy'])->name('api.system.users.destroy');
    
    Route::post('/users/{user_id}/activate', [SystemUserController::class, 'activate'])->name('api.system.users.activate');
    Route::post('/users/{user_id}/deactivate', [SystemUserController::class, 'deactivate'])->name('api.system.users.deactivate');
    Route::post('/users/{user_id}/confirm', [SystemUserController::class, 'confirm'])->name('api.system.users.confirm');
    Route::post('/users/{user_id}/unconfirm', [SystemUserController::class, 'unconfirm'])->name('api.system.users.unconfirm');
});