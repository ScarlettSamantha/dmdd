<?php

namespace Scarlett\DMDD\GUI\App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;
use Scarlett\DMDD\GUI\Http\Controllers\ApiController;

class VersionController extends ApiController
{
    /**
     * Get the application version.
     *
     * @param BackendIntegrationService $backendIntegrationService
     * @return JsonResponse
     */
    public function getVersion(BackendIntegrationService $backendIntegrationService): JsonResponse
    {
        try {
            $version = $backendIntegrationService->getFormattedVersion();
            return response()->json($version); // Respond with the formatted version as JSON
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500); // Handle exceptions gracefully
        }
    }
}