<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Repositories;

use Scarlett\DMDD\GUI\Models\Library;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

/**
 * Repository class for managing libraries via the backend integration service.
 */
class LibraryRepository
{
    private BackendIntegrationService $backendIntegrationService;

    /**
     * Constructor for LibraryRepository.
     *
     * @param BackendIntegrationService $backendIntegrationService Integration service for backend API.
     */
    public function __construct(BackendIntegrationService $backendIntegrationService)
    {
        $this->backendIntegrationService = $backendIntegrationService;
    }

    /**
     * Shortcut for a success response.
     *
     * @param mixed $data The response data.
     * @param int $statusCode HTTP status code (default: 200).
     * @return BackendIntegrationServiceResponse
     */
    private function success(mixed $data = null, int $statusCode = 200): BackendIntegrationServiceResponse
    {
        return new BackendIntegrationServiceResponse(data: $data, status_code: $statusCode);
    }

    /**
     * Shortcut for an error response.
     *
     * @param string|null $message The error message.
     * @param int $statusCode HTTP status code.
     * @return BackendIntegrationServiceResponse
     */
    private function error(?string $message, int $statusCode): BackendIntegrationServiceResponse
    {
        return new BackendIntegrationServiceResponse(data: null, status_code: $statusCode, message: $message);
    }

    /**
     * Transform raw data into a Library model.
     *
     * @param mixed $data
     * @return Library|null
     */
    private function transformToModel(mixed $data): ?Library
    {
        return $data ? Library::fromArray((array) $data) : null;
    }

    /**
     * Retrieve a library by ID.
     *
     * @param string $libraryId The unique identifier of the library.
     * @return BackendIntegrationServiceResponse
     */
    public function getById(string $libraryId): BackendIntegrationServiceResponse
    {
        try {
            $library = $this->backendIntegrationService->getLibrary($libraryId);
            return $this->success($this->transformToModel($library));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Retrieve all libraries.
     *
     * @return BackendIntegrationServiceResponse
     */
    public function getAll(): BackendIntegrationServiceResponse
    {
        try {
            $libraries = $this->backendIntegrationService->getLibraries();
            $models = array_map(fn($library) => $this->transformToModel($library), $libraries);
            return $this->success($models);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Create a new library.
     *
     * @param array<string, mixed> $data The data for creating the library.
     * @return BackendIntegrationServiceResponse
     */
    public function create(array $data): BackendIntegrationServiceResponse
    {
        try {
            $library = $this->backendIntegrationService->createLibrary($data);
            return $this->success($this->transformToModel($library), 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Update an existing library.
     *
     * @param string $libraryId The unique identifier of the library.
     * @param array<string, mixed> $data The data for updating the library.
     * @return BackendIntegrationServiceResponse
     */
    public function update(string $libraryId, array $data): BackendIntegrationServiceResponse
    {
        try {
            $library = $this->backendIntegrationService->updateLibrary($libraryId, $data);
            return $this->success($this->transformToModel($library));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Delete a library by ID.
     *
     * @param string $libraryId The unique identifier of the library.
     * @return BackendIntegrationServiceResponse
     */
    public function delete(string $libraryId): BackendIntegrationServiceResponse
    {
        try {
            $this->backendIntegrationService->deleteLibrary($libraryId);
            return $this->success(null, 204);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}