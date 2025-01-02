<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers;

use Scarlett\DMDD\GUI\Http\Controllers\Controller;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Helper function to format responses from BackendIntegrationServiceResponse.
     */
    public function formatResponse(?BackendIntegrationServiceResponse $response, int $defaultStatusCode = Response::HTTP_OK): JsonResponse
    {
        if ($response === null) {
            return new JsonResponse(null, $defaultStatusCode);
        }
        return new JsonResponse($response->toArray(), $response->status_code ?? $defaultStatusCode);
    }
}