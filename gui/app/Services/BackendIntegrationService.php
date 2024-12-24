<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Services;

use Illuminate\Support\Facades\Http;

class BackendIntegrationService
{
    CONST ENDPOINT_VERSION = '/version';

    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.dmdd.api_url');
    }

    public function fetchData(string $endpoint): array
    {
        $response = Http::get($this->baseUrl . $endpoint);

        if ($response->failed()) {
            throw new \Exception("Failed to fetch data from API.");
        }

        return $response->json();
    }

    public function sendData(string $endpoint, array $data): array
    {
        $response = Http::post($this->baseUrl . $endpoint, $data);

        if ($response->failed()) {
            throw new \Exception("Failed to send data to API.");
        }

        return $response->json();
    }

    public function getVersion(): array
    {
        return $this->fetchData(static::ENDPOINT_VERSION);
    }

    private function parseVersion(array $version): array
    {
        $parts = explode('.', $version['version']);
        return [
            'major' => (int) $parts[0],
            'minor' => (int) $parts[1],
            'patch' => (int) $parts[2],
            'releaselevel' => (string) $version['releaselevel'],
            'serial' => (int) $version['serial'],
        ];
    }

    public function getFormattedVersion(): array
    {
        $version = $this->getVersion();
        return $this->parseVersion($version);
    }
}