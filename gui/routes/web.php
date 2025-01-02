<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\AuthController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\DashboardController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\LibraryController;
use Scarlett\DMDD\GUI\Http\Controllers\Gui\LibraryItemController;
use Scarlett\DMDD\GUI\Http\Controllers\Api\LibraryController as ApiLibraryController;
use Scarlett\DMDD\GUI\Http\Controllers\Api\LibraryItemController as ApiLibraryItemController;
use Scarlett\DMDD\GUI\Http\Controllers\Api\SystemUserController;
use Scarlett\DMDD\GUI\Http\Controllers\Api\VersionController;
use Scarlett\DMDD\GUI\Http\Middleware\Authenticate;

# Web GUI authentication
Route::middleware(['web'])->group(function () {
    Route::get('/auth/login', [AuthController::class, 'get'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/auth/logout', [AuthController::class, 'logout'])->name('logout');
});

# Web GUI
Route::middleware([Authenticate::class])->group(function () {
    Route::get('/', [DashboardController::class, 'get'])->name('dashboard')->name('home');

    # CRUD for System Users
    Route::prefix('users')->group(function () {
        Route::get('/', [SystemUserController::class, 'index'])->name('gui.users.index');
        Route::get('/create', [SystemUserController::class, 'create'])->name('gui.users.create');
        Route::post('/', [SystemUserController::class, 'store'])->name('gui.users.store');
        Route::get('/{user_id}', [SystemUserController::class, 'show'])->name('gui.users.show');
        Route::get('/{user_id}/edit', [SystemUserController::class, 'edit'])->name('gui.users.edit');
        Route::put('/{user_id}', [SystemUserController::class, 'update'])->name('gui.users.update');
        Route::delete('/{user_id}', [SystemUserController::class, 'destroy'])->name('gui.users.destroy');
    });

    # CRUD for Libraries
    Route::prefix('libraries')->group(function () {
        Route::get('/', [LibraryController::class, 'index'])->name('gui.libraries.index');
        Route::get('/create', [LibraryController::class, 'create'])->name('gui.libraries.create');
        Route::post('/', [LibraryController::class, 'store'])->name('gui.libraries.store');
        Route::get('/{library_id}', [LibraryController::class, 'show'])->name('gui.libraries.show');
        Route::get('/{library_id}/edit', [LibraryController::class, 'edit'])->name('gui.libraries.edit');
        Route::put('/{library_id}', [LibraryController::class, 'update'])->name('gui.libraries.update');
        Route::delete('/{library_id}', [LibraryController::class, 'destroy'])->name('gui.libraries.destroy');

        # CRUD for Library Items
        Route::prefix('{library_id}/items')->group(function () {
            Route::get('/', [LibraryItemController::class, 'index'])->name('gui.library_items.index');
            Route::get('/create', [LibraryItemController::class, 'create'])->name('gui.library_items.create');
            Route::post('/', [LibraryItemController::class, 'store'])->name('gui.library_items.store');
            Route::get('/{item_id}', [LibraryItemController::class, 'show'])->name('gui.library_items.show');
            Route::get('/{item_id}/edit', [LibraryItemController::class, 'edit'])->name('gui.library_items.edit');
            Route::put('/{item_id}', [LibraryItemController::class, 'update'])->name('gui.library_items.update');
            Route::delete('/{item_id}', [LibraryItemController::class, 'destroy'])->name('gui.library_items.destroy');
        });
    });
});

# API
Route::prefix('api')->middleware(['api'])->group(function () {
    # API version and health check
    Route::get('/version', [VersionController::class, 'getVersion']);

    # CRUD API for Libraries
    Route::prefix('libraries')->group(function () {
        Route::get('/', [ApiLibraryController::class, 'index'])->name('api.libraries.index');
        Route::post('/', [ApiLibraryController::class, 'store'])->name('api.libraries.store');
        Route::get('/{library_id}', [ApiLibraryController::class, 'show'])->name('api.libraries.show');
        Route::put('/{library_id}', [ApiLibraryController::class, 'update'])->name('api.libraries.update');
        Route::delete('/{library_id}', [ApiLibraryController::class, 'destroy'])->name('api.libraries.destroy');

        # CRUD API for Library Items
        Route::prefix('{library_id}/items')->group(function () {
            Route::get('/', [ApiLibraryItemController::class, 'index'])->name('api.library_items.index');
            Route::post('/', [ApiLibraryItemController::class, 'store'])->name('api.library_items.store');
            Route::get('/{library_item_id}', [ApiLibraryItemController::class, 'show'])->name('api.library_items.show');
            Route::put('/{library_item_id}', [ApiLibraryItemController::class, 'update'])->name('api.library_items.update');
            Route::delete('/{library_item_id}', [ApiLibraryItemController::class, 'destroy'])->name('api.library_items.destroy');
        });
    });

    # CRUD API for System Users
    Route::prefix('system')->group(function () {
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
});
