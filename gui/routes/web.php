<?php
declare(strict_types=1);

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Route;

use Gui\App\Http\Controllers\Api\VersionController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\AuthController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\DashboardController;

use Scarlett\DMDD\GUI\Http\Middleware\Authenticate;

# Web gui
Route::get('/', [DashboardController::class, 'get'])->middleware([Authenticate::class])->name('dashboard');
Route::get('/auth/login', [AuthController::class, 'get'])->name('login');

# Api, version and health check
Route::get('/api/version', [VersionController::class, 'getVersion']);