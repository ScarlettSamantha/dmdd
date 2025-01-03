<?php

use Tests\TestCase;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;

class BackendIntegrationServiceTest extends TestCase
{
    private BackendIntegrationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure the API URL for testing
        config(['services.dmdd.api_url' => 'https://api.dmdd-core.local']);
    }

    public function testGetVersion(): void
    {
        $mockResponse = [
            'version' => '1.2.3',
            'releaselevel' => 'stable',
            'serial' => 1,
        ];

        $this->mock(BackendIntegrationService::class, function ($mock) use ($mockResponse) {
            $mock->shouldReceive('getVersion')->once()->andReturn($mockResponse);
        });

        $service = app(BackendIntegrationService::class);
        $response = $service->getVersion();

        $this->assertSame($mockResponse, $response);
    }

    public function testGetFormattedVersion(): void
    {
        $mockResponse = [
            'major' => 1,
            'minor' => 2,
            'patch' => 3,
            'releaselevel' => 'stable',
            'serial' => 1,
        ];

        $this->mock(BackendIntegrationService::class, function ($mock) use ($mockResponse) {
            $mock->shouldReceive('getFormattedVersion')->once()->andReturn($mockResponse);
        });

        $service = app(BackendIntegrationService::class);
        $response = $service->getFormattedVersion();

        $this->assertSame($mockResponse, $response);
    }

    public function testGetSystemUsers(): void
    {
        $mockResponse = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Doe'],
        ];

        $this->mock(BackendIntegrationService::class, function ($mock) use ($mockResponse) {
            $mock->shouldReceive('getSystemUsers')->once()->andReturn($mockResponse);
        });

        $service = app(BackendIntegrationService::class);
        $response = $service->getSystemUsers();

        $this->assertSame($mockResponse, $response);
    }

    public function testCreateSystemUser(): void
    {
        $userData = ['name' => 'John Doe', 'email' => 'john.doe@example.com'];
        $mockResponse = ['id' => 1, 'name' => 'John Doe', 'email' => 'john.doe@example.com'];

        $this->mock(BackendIntegrationService::class, function ($mock) use ($mockResponse, $userData) {
            $mock->shouldReceive('createSystemUser')->once()->with($userData)->andReturn($mockResponse);
        });

        $service = app(BackendIntegrationService::class);
        $response = $service->createSystemUser($userData);

        $this->assertSame($mockResponse, $response);
    }

    public function testGetSystemUser(): void
    {
        $userId = '1';
        $mockResponse = ['id' => 1, 'name' => 'John Doe', 'email' => 'john.doe@example.com'];

        $this->mock(BackendIntegrationService::class, function ($mock) use ($mockResponse, $userId) {
            $mock->shouldReceive('getSystemUser')->once()->with($userId)->andReturn($mockResponse);
        });

        $service = app(BackendIntegrationService::class);
        $response = $service->getSystemUser($userId);

        $this->assertSame($mockResponse, $response);
    }

    public function testUpdateSystemUser(): void
    {
        $userId = '1';
        $userData = ['name' => 'John Updated Doe', 'email' => 'updated.john.doe@example.com'];
        $mockResponse = ['id' => 1, 'name' => 'John Updated Doe', 'email' => 'updated.john.doe@example.com'];

        $this->mock(BackendIntegrationService::class, function ($mock) use ($mockResponse, $userId, $userData) {
            $mock->shouldReceive('updateSystemUser')->once()->with($userId, $userData)->andReturn($mockResponse);
        });

        $service = app(BackendIntegrationService::class);
        $response = $service->updateSystemUser($userId, $userData);

        $this->assertSame($mockResponse, $response);
    }

    public function testDeleteSystemUser(): void
    {
        $userId = '1';
        $mockResponse = ['success' => true];

        $this->mock(BackendIntegrationService::class, function ($mock) use ($mockResponse, $userId) {
            $mock->shouldReceive('deleteSystemUser')->once()->with($userId)->andReturn($mockResponse);
        });

        $service = app(BackendIntegrationService::class);
        $response = $service->deleteSystemUser($userId);

        $this->assertSame($mockResponse, $response);
    }
}
