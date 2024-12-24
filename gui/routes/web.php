<?php

use Illuminate\Support\Facades\Route;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;

use Gui\App\Http\Controllers\Api\VersionController;

Route::get('/api/version', [VersionController::class, 'getVersion']);