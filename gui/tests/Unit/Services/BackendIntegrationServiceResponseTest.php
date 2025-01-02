<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Tests\Services;

use Tests\TestCase;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

class BackendIntegrationServiceResponseTest extends TestCase
{
    public function testConstructorInitializesProperties(): void
    {
        $data = ['key' => 'value'];
        $statusCode = 201;
        $message = 'Created successfully';

        $response = new BackendIntegrationServiceResponse($data, $statusCode, $message);

        $this->assertSame($data, $response->data);
        $this->assertSame($statusCode, $response->status_code);
        $this->assertSame($message, $response->message);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $response = new BackendIntegrationServiceResponse();

        $this->assertNull($response->data);
        $this->assertSame(200, $response->status_code);
        $this->assertNull($response->message);
    }

    public function testToArray(): void
    {
        $data = ['key' => 'value'];
        $statusCode = 400;
        $message = 'Bad Request';

        $response = new BackendIntegrationServiceResponse($data, $statusCode, $message);
        $expectedArray = [
            'data' => $data,
            'status_code' => $statusCode,
            'message' => $message,
        ];

        $this->assertSame($expectedArray, $response->toArray());
    }

    public function testToArrayWithDefaultValues(): void
    {
        $response = new BackendIntegrationServiceResponse();
        $expectedArray = [
            'data' => null,
            'status_code' => 200,
            'message' => null,
        ];

        $this->assertSame($expectedArray, $response->toArray());
    }
}
