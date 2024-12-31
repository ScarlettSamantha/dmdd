<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Repositories;

use Scarlett\DMDD\GUI\Services\BackendIntegrationService;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

/**
 * Repository class for managing system users via the backend integration service.
 */
class SystemUserRepository
{
    private BackendIntegrationService $backendIntegrationService;

    /**
     * Constructor for SystemUserRepository.
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
     * Retrieve a user by ID.
     *
     * @param string $userId The unique identifier of the user.
     * @return BackendIntegrationServiceResponse
     */
    public function getById(string $userId): BackendIntegrationServiceResponse
    {
        try {
            $user = $this->backendIntegrationService->getUser($userId);
            return $this->success($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Retrieve all users.
     *
     * @return BackendIntegrationServiceResponse
     */
    public function getAll(): BackendIntegrationServiceResponse
    {
        try {
            $users = $this->backendIntegrationService->getUsers();
            return $this->success($users);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Create a new user.
     *
     * @param array<string, mixed> $data The data for creating the user.
     * @return BackendIntegrationServiceResponse
     */
    public function create(array $data): BackendIntegrationServiceResponse
    {
        try {
            $user = $this->backendIntegrationService->createUser($data);
            return $this->success($user, 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Update an existing user.
     *
     * @param string $userId The unique identifier of the user.
     * @param array<string, mixed> $data The data for updating the user.
     * @return BackendIntegrationServiceResponse
     */
    public function update(string $userId, array $data): BackendIntegrationServiceResponse
    {
        try {
            $user = $this->backendIntegrationService->updateUser($userId, $data);
            return $this->success($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Delete a user by ID.
     *
     * @param string $userId The unique identifier of the user.
     * @return BackendIntegrationServiceResponse
     */
    public function delete(string $userId): BackendIntegrationServiceResponse
    {
        try {
            $this->backendIntegrationService->deleteUser($userId);
            return $this->success(null, 204);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Activate a user (set is_active = true).
     *
     * @param string $userId The unique identifier of the user.
     * @return BackendIntegrationServiceResponse
     */
    public function activate(string $userId): BackendIntegrationServiceResponse
    {
        try {
            $user = $this->backendIntegrationService->activateUser($userId);
            return $this->success($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Deactivate a user (set is_active = false).
     *
     * @param string $userId The unique identifier of the user.
     * @return BackendIntegrationServiceResponse
     */
    public function deactivate(string $userId): BackendIntegrationServiceResponse
    {
        try {
            $user = $this->backendIntegrationService->deactivateUser($userId);
            return $this->success($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Confirm a user (set is_confirmed = true).
     *
     * @param string $userId The unique identifier of the user.
     * @return BackendIntegrationServiceResponse
     */
    public function confirm(string $userId): BackendIntegrationServiceResponse
    {
        try {
            $user = $this->backendIntegrationService->confirmUser($userId);
            return $this->success($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Unconfirm a user (set is_confirmed = false).
     *
     * @param string $userId The unique identifier of the user.
     * @return BackendIntegrationServiceResponse
     */
    public function unconfirm(string $userId): BackendIntegrationServiceResponse
    {
        try {
            $user = $this->backendIntegrationService->unconfirmUser($userId);
            return $this->success($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
