<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Repositories;

use Scarlett\DMDD\GUI\Models\LibraryItem;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

/**
 * Repository class for managing library items via the backend integration service.
 */
class LibraryItemRepository
{
    private BackendIntegrationService $backendIntegrationService;

    /**
     * Constructor for LibraryItemRepository.
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
     * Transform raw data into a LibraryItem model.
     *
     * @param mixed $data
     * @return LibraryItem|null
     */
    private function transformToModel(mixed $data): ?LibraryItem
    {
        return $data ? LibraryItem::fromArray((array) $data) : null;
    }

    /**
     * Retrieve an item by ID within a library.
     *
     * @param string $libraryId The unique identifier of the library.
     * @param string $itemId The unique identifier of the item.
     * @return BackendIntegrationServiceResponse
     */
    public function getById(string $libraryId, string $itemId): BackendIntegrationServiceResponse
    {
        try {
            /** */
            $item = $this->backendIntegrationService->getLibraryItem($libraryId, $itemId);
            return $this->success($this->transformToModel($item));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Retrieve all items within a library.
     *
     * @param string $libraryId The unique identifier of the library.
     * @return BackendIntegrationServiceResponse
     */
    public function getAll(string $libraryId): BackendIntegrationServiceResponse
    {
        try {
            $items = $this->backendIntegrationService->getLibraryItems($libraryId);
            $models = array_map(fn($item) => $this->transformToModel($item), $items);
            return $this->success($models);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Create a new item within a library.
     *
     * @param string $libraryId The unique identifier of the library.
     * @param array<string, mixed> $data The data for creating the item.
     * @return BackendIntegrationServiceResponse
     */
    public function create(string $libraryId, array $data): BackendIntegrationServiceResponse
    {
        try {
            $item = $this->backendIntegrationService->createLibraryItem($libraryId, $data);
            return $this->success($this->transformToModel($item), 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Update an existing item within a library.
     *
     * @param string $libraryId The unique identifier of the library.
     * @param string $itemId The unique identifier of the item.
     * @param array<string, mixed> $data The data for updating the item.
     * @return BackendIntegrationServiceResponse
     */
    public function update(string $libraryId, string $itemId, array $data): BackendIntegrationServiceResponse
    {
        try {
            $item = $this->backendIntegrationService->updateLibraryItem($libraryId, $itemId, $data);
            return $this->success($this->transformToModel($item));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Delete an item by ID within a library.
     *
     * @param string $libraryId The unique identifier of the library.
     * @param string $itemId The unique identifier of the item.
     * @return BackendIntegrationServiceResponse
     */
    public function delete(string $libraryId, string $itemId): BackendIntegrationServiceResponse
    {
        try {
            $this->backendIntegrationService->deleteLibraryItem($libraryId, $itemId);
            return $this->success(null, 204);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
