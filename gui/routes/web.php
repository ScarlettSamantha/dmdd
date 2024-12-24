<?php

use Illuminate\Support\Facades\Route;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;

Route::get('/version', function (BackendIntegrationService $backendIntegrationService) {
    try {
        $version = $backendIntegrationService->getFormattedVersion();
        return response()->json($version); // Respond with the formatted version as JSON
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500); // Handle exceptions gracefully
    }
});