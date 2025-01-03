<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Tests\Services;

use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;
use Symfony\Component\ErrorHandler\ErrorHandler;

class BackendIntegrationServiceTest extends TestCase
{
    private BackendIntegrationService $service;
    private string $apiUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiUrl = 'https://dmdd-core';
        $this->service = new BackendIntegrationService();
    }

    #[WithoutErrorHandler]
    public function testFetchDataSuccess(): void
    {
        $endpoint = '/test-endpoint';
        $mockResponse = ['key' => 'value'];

        Http::shouldReceive('get')
            ->once()
            ->with($this->apiUrl . $endpoint)
            ->andReturn(Http::response($mockResponse, 200));

        $response = $this->service->fetchData($endpoint);

        $this->assertSame($mockResponse, $response);
        restore_error_handler();
        restore_exception_handler();
    }

    #[WithoutErrorHandler]
    public function testFetchDataFailure(): void
    {
        $endpoint = '/test-endpoint';

        Http::shouldReceive('get')
            ->once()
            ->with($this->apiUrl . $endpoint)
            ->andReturn(Http::response([], 500));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Failed to fetch data from API: {$endpoint}");

        $this->service->fetchData($endpoint);
    }

    #[WithoutErrorHandler]
    public function testSendDataSuccess(): void
    {
        $endpoint = '/test-endpoint';
        $data = ['key' => 'value'];
        $mockResponse = ['result' => 'success'];

        Http::shouldReceive('post')
            ->once()
            ->with($this->apiUrl . $endpoint, $data)
            ->andReturn(Http::response($mockResponse, 200));

        $response = $this->service->sendData($endpoint, $data);

        $this->assertSame($mockResponse, $response);
    }

    #[WithoutErrorHandler]
    public function testSendDataFailure(): void
    {
        $endpoint = '/test-endpoint';
        $data = ['key' => 'value'];

        Http::shouldReceive('post')
            ->once()
            ->with($this->apiUrl . $endpoint, $data)
            ->andReturn(Http::response([], 400));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Failed to send data to API: {$endpoint}");

        $this->service->sendData($endpoint, $data);
    }

    #[WithoutErrorHandler]
    public function testPutDataSuccess(): void
    {
        $endpoint = '/test-endpoint';
        $data = ['key' => 'value'];
        $mockResponse = ['result' => 'success'];

        Http::shouldReceive('put')
            ->once()
            ->with($this->apiUrl . $endpoint, $data)
            ->andReturn(Http::response($mockResponse, 200));

        $response = $this->service->putData($endpoint, $data);

        $this->assertSame($mockResponse, $response);
    }

    #[WithoutErrorHandler]
    public function testDeleteDataSuccess(): void
    {
        $endpoint = '/test-endpoint';
        $mockResponse = ['result' => 'deleted'];

        Http::shouldReceive('delete')
            ->once()
            ->with($this->apiUrl . $endpoint)
            ->andReturn(Http::response($mockResponse, 200));

        $response = $this->service->deleteData($endpoint);

        $this->assertSame($mockResponse, $response);
    }

    #[WithoutErrorHandler]
    public function testDeleteDataFailure(): void
    {
        $endpoint = '/test-endpoint';

        Http::shouldReceive('delete')
            ->once()
            ->with($this->apiUrl . $endpoint)
            ->andReturn(Http::response([], 400));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Failed to delete data on API: {$endpoint}");

        $this->service->deleteData($endpoint);
    }

    #[WithoutErrorHandler]
    public function testGetVersion(): void
    {
        $mockVersion = ['version' => '1.2.3', 'releaselevel' => 'stable', 'serial' => 1];

        Http::shouldReceive('get')
            ->once()
            ->with($this->apiUrl . BackendIntegrationService::ENDPOINT_VERSION)
            ->andReturn(Http::response($mockVersion, 200));

        $response = $this->service->getVersion();

        $this->assertSame($mockVersion, $response);
    }

    #[WithoutErrorHandler]
    public function testGetFormattedVersion(): void
    {
        $mockVersion = ['version' => '1.2.3', 'releaselevel' => 'stable', 'serial' => 1];

        Http::shouldReceive('get')
            ->once()
            ->with($this->apiUrl . BackendIntegrationService::ENDPOINT_VERSION)
            ->andReturn(Http::response($mockVersion, 200));

        $response = $this->service->getFormattedVersion();

        $this->assertSame(
            ['major' => 1, 'minor' => 2, 'patch' => 3, 'releaselevel' => 'stable', 'serial' => 1],
            $response
        );
    }

    protected function tearDown(): void
    {
        // Restore default exception handler
        set_exception_handler(null);
        set_error_handler(null);

        parent::tearDown();
    }
}