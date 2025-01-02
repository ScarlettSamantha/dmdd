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
    public const ENDPOINT_LIBRARIES = '/api/libraries';
    public const ENDPOINT_LIBRARY = '/api/libraries/{library_id}';
    public const ENDPOINT_LIBRARY_ITEMS = '/api/libraries/{library_id}/items';
    public const ENDPOINT_LIBRARY_ITEM = '/api/libraries/{library_id}/items/{item_id}';
    public const ENDPOINT_SYSTEM_USERS = '/api/system/users';
    public const ENDPOINT_SYSTEM_USER = '/api/system/users/{user_id}';
    public const ENDPOINT_SYSTEM_USER_ACTIVATE = '/api/system/users/{user_id}/activate';
    public const ENDPOINT_SYSTEM_USER_DEACTIVATE = '/api/system/users/{user_id}/deactivate';
    public const ENDPOINT_SYSTEM_USER_CONFIRM = '/api/system/users/{user_id}/confirm';
    public const ENDPOINT_SYSTEM_USER_UNCONFIRM = '/api/system/users/{user_id}/unconfirm';


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
     | SystemUser-related endpoints that proxy to dmdd-core
     |--------------------------------------------------------------------------
     */

    /**
     * GET /api/system/users
     * Retrieves all system users from dmdd-core.
     */
    public function getSystemUsers(): array
    {
        return $this->fetchData(static::ENDPOINT_SYSTEM_USERS);
    }

    /**
     * POST /api/system/users
     * Creates a new system user in dmdd-core.
     */
    public function createSystemUser(array $userData): array
    {
        return $this->sendData(static::ENDPOINT_SYSTEM_USERS, $userData);
    }

    /**
     * GET /api/system/users/{user_id}
     * Fetches a single system user from dmdd-core.
     */
    public function getSystemUser(string $userId): array
    {
        $endpoint = str_replace('{user_id}', $userId, static::ENDPOINT_SYSTEM_USER);
        return $this->fetchData($endpoint);
    }

    /**
     * PUT /api/system/users/{user_id}
     * Updates an existing system user in dmdd-core.
     */
    public function updateSystemUser(string $userId, array $userData): array
    {
        $endpoint = str_replace('{user_id}', $userId, static::ENDPOINT_SYSTEM_USER);
        return $this->putData($endpoint, $userData);
    }

    /**
     * DELETE /api/system/users/{user_id}
     * Deletes a system user by ID in dmdd-core.
     */
    public function deleteSystemUser(string $userId): array
    {
        $endpoint = str_replace('{user_id}', $userId, static::ENDPOINT_SYSTEM_USER);
        return $this->deleteData($endpoint);
    }

       /**
     * Activate a system user account.
     * 
     * PATCH /api/system/users/{user_id}/activate
     */
    public function activateSystemUser(string $userId): array
    {
        $endpoint = str_replace('{user_id}', $userId, static::ENDPOINT_SYSTEM_USER_ACTIVATE);
        return $this->sendData($endpoint, []);
    }

    /**
     * Deactivate a system user account.
     * 
     * PATCH /api/system/users/{user_id}/deactivate
     */
    public function deactivateSystemUser(string $userId): array
    {
        $endpoint = str_replace('{user_id}', $userId, static::ENDPOINT_SYSTEM_USER_DEACTIVATE);
        return $this->sendData($endpoint, []);
    }

    /**
     * Confirm a system user account.
     * 
     * PATCH /api/system/users/{user_id}/confirm
     */
    public function confirmSystemUser(string $userId): array
    {
        $endpoint = str_replace('{user_id}', $userId, static::ENDPOINT_SYSTEM_USER_CONFIRM);
        return $this->sendData($endpoint, []);
    }

    /**
     * Unconfirm a system user account.
     * 
     * PATCH /api/system/users/{user_id}/unconfirm
     */
    public function unconfirmSystemUser(string $userId): array
    {
        $endpoint = str_replace('{user_id}', $userId, static::ENDPOINT_SYSTEM_USER_UNCONFIRM);
        return $this->sendData($endpoint, []);
    }

    /*
     |--------------------------------------------------------------------------
     | Library-related endpoints that proxy to dmdd-core
     |--------------------------------------------------------------------------
     */

    /**
     * GET /api/libraries
     * Retrieves all libraries from dmdd-core.
     */
    public function getLibraries(): array
    {
        return $this->fetchData(static::ENDPOINT_LIBRARIES);
    }

    /**
     * POST /api/libraries
     * Creates a new library in dmdd-core.
     */
    public function createLibrary(array $libraryData): array
    {
        return $this->sendData(static::ENDPOINT_LIBRARIES, $libraryData);
    }

    /**
     * GET /api/libraries/{library_id}
     * Fetches a single library from dmdd-core.
     */
    public function getLibrary(string $libraryId): array
    {
        $endpoint = str_replace('{library_id}', $libraryId, static::ENDPOINT_LIBRARY);
        return $this->fetchData($endpoint);
    }

    /**
     * PUT /api/libraries/{library_id}
     * Updates an existing library in dmdd-core.
     */
    public function updateLibrary(string $libraryId, array $libraryData): array
    {
        $endpoint = str_replace('{library_id}', $libraryId, static::ENDPOINT_LIBRARY);
        return $this->putData($endpoint, $libraryData);
    }

    /**
     * DELETE /api/libraries/{library_id}
     * Deletes a library by ID in dmdd-core.
     */
    public function deleteLibrary(string $libraryId): array
    {
        $endpoint = str_replace('{library_id}', $libraryId, static::ENDPOINT_LIBRARY);
        return $this->deleteData($endpoint);
    }

    /*
     |--------------------------------------------------------------------------
     | Library Item-related endpoints that proxy to dmdd-core
     |--------------------------------------------------------------------------
     */

    /**
     * GET /api/libraries/{library_id}/items
     * Retrieves all items for a library from dmdd-core.
     */
    public function getLibraryItems(string $libraryId): array
    {
        $endpoint = str_replace('{library_id}', $libraryId, static::ENDPOINT_LIBRARY_ITEMS);
        return $this->fetchData($endpoint);
    }

    /**
     * POST /api/libraries/{library_id}/items
     * Creates a new item in a library in dmdd-core.
     */
    public function createLibraryItem(string $libraryId, array $itemData): array
    {
        $endpoint = str_replace('{library_id}', $libraryId, static::ENDPOINT_LIBRARY_ITEMS);
        return $this->sendData($endpoint, $itemData);
    }

    /**
     * GET /api/libraries/{library_id}/items/{item_id}
     * Fetches a single item from a library in dmdd-core.
     */
    public function getLibraryItem(string $libraryId, string $itemId): array
    {
        $endpoint = str_replace(['{library_id}', '{item_id}'], [$libraryId, $itemId], static::ENDPOINT_LIBRARY_ITEM);
        return $this->fetchData($endpoint);
    }

    /**
     * PUT /api/libraries/{library_id}/items/{item_id}
     * Updates an existing item in a library in dmdd-core.
     */
    public function updateLibraryItem(string $libraryId, string $itemId, array $itemData): array
    {
        $endpoint = str_replace(['{library_id}', '{item_id}'], [$libraryId, $itemId], static::ENDPOINT_LIBRARY_ITEM);
        return $this->putData($endpoint, $itemData);
    }

    /**
     * DELETE /api/libraries/{library_id}/items/{item_id}
     * Deletes an item by ID in a library in dmdd-core.
     */
    public function deleteLibraryItem(string $libraryId, string $itemId): array
    {
        $endpoint = str_replace(['{library_id}', '{item_id}'], [$libraryId, $itemId], static::ENDPOINT_LIBRARY_ITEM);
        return $this->deleteData($endpoint);
    }
}