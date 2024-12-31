<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Services;

use Illuminate\Support\Facades\Http;

class BackendIntegrationService
{
    /**
     * Example endpoint for version checks.
     */
    public const ENDPOINT_VERSION = '/version';

    /**
     * Base URL taken from config('services.dmdd.api_url').
     */
    private string $baseUrl;

    /**
     * Constructor sets the base URL for the dmdd-core (Python) backend.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.dmdd.api_url');
    }

    /**
     * Fetch data via GET request.
     */
    public function fetchData(string $endpoint): array
    {
        $response = Http::get($this->baseUrl . $endpoint);

        if ($response->failed()) {
            throw new \Exception("Failed to fetch data from API: {$endpoint}");
        }

        return $response->json();
    }

    /**
     * Send data via POST request.
     */
    public function sendData(string $endpoint, array $data): array
    {
        $response = Http::post($this->baseUrl . $endpoint, $data);

        if ($response->failed()) {
            throw new \Exception("Failed to send data to API: {$endpoint}");
        }

        return $response->json();
    }

    /**
     * Send data via PUT request.
     */
    public function putData(string $endpoint, array $data): array
    {
        $response = Http::put($this->baseUrl . $endpoint, $data);

        if ($response->failed()) {
            throw new \Exception("Failed to update data to API via PUT: {$endpoint}");
        }

        return $response->json();
    }

    /**
     * Send a DELETE request.
     */
    public function deleteData(string $endpoint): array
    {
        $response = Http::delete($this->baseUrl . $endpoint);

        if ($response->failed()) {
            throw new \Exception("Failed to delete data on API: {$endpoint}");
        }

        // Some APIs return an empty body on success (e.g., 204 No Content).
        // We'll attempt to parse JSON anyway; if empty, handle gracefully.
        return $response->json() ?: [];
    }

    /**
     * Retrieve the version details from dmdd-core.
     */
    public function getVersion(): array
    {
        return $this->fetchData(static::ENDPOINT_VERSION);
    }

    /**
     * Parse the dmdd-core version payload.
     */
    private function parseVersion(array $version): array
    {
        $parts = explode('.', $version['version']);
        return [
            'major'        => (int) $parts[0],
            'minor'        => (int) $parts[1],
            'patch'        => (int) $parts[2],
            'releaselevel' => (string) $version['releaselevel'],
            'serial'       => (int) $version['serial'],
        ];
    }

    /**
     * Return a structured version array from the dmdd-core service.
     */
    public function getFormattedVersion(): array
    {
        $version = $this->getVersion();
        return $this->parseVersion($version);
    }

    /*
     |--------------------------------------------------------------------------
     | Below are User-related endpoints that proxy to dmdd-core
     |--------------------------------------------------------------------------
     */

    /**
     * GET /api/system/users
     * Retrieves all users from dmdd-core.
     */
    public function getUsers(): array
    {
        return $this->fetchData('/api/system/users');
    }

    /**
     * POST /api/system/users
     * Creates a new user in dmdd-core.
     */
    public function createUser(array $userData): array
    {
        return $this->sendData('/api/system/users', $userData);
    }

    /**
     * GET /api/system/users/{user_id}
     * Fetches a single user from dmdd-core.
     */
    public function getUser(string $userId): array
    {
        return $this->fetchData("/api/system/users/{$userId}");
    }

    /**
     * PUT /api/system/users/{user_id}
     * Updates an existing user in dmdd-core.
     */
    public function updateUser(string $userId, array $userData): array
    {
        return $this->putData("/api/system/users/{$userId}", $userData);
    }

    /**
     * DELETE /api/system/users/{user_id}
     * Deletes a user by ID in dmdd-core.
     */
    public function deleteUser(string $userId): array
    {
        return $this->deleteData("/api/system/users/{$userId}");
    }

    /**
     * POST /api/system/users/{user_id}/activate
     * Activates a user (is_active = true).
     */
    public function activateUser(string $userId): array
    {
        return $this->sendData("/api/system/users/{$userId}/activate", []);
    }

    /**
     * POST /api/system/users/{user_id}/deactivate
     * Deactivates a user (is_active = false).
     */
    public function deactivateUser(string $userId): array
    {
        return $this->sendData("/api/system/users/{$userId}/deactivate", []);
    }

    /**
     * POST /api/system/users/{user_id}/confirm
     * Confirms a user (is_confirmed = true).
     */
    public function confirmUser(string $userId): array
    {
        return $this->sendData("/api/system/users/{$userId}/confirm", []);
    }

    /**
     * POST /api/system/users/{user_id}/unconfirm
     * Unconfirms a user (is_confirmed = false).
     */
    public function unconfirmUser(string $userId): array
    {
        return $this->sendData("/api/system/users/{$userId}/unconfirm", []);
    }
}