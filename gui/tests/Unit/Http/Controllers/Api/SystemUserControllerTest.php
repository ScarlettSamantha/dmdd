<?php

namespace Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Scarlett\DMDD\GUI\App\Http\Controllers\Api\SystemUserController;
use Scarlett\DMDD\GUI\Repositories\SystemUserRepository;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

class SystemUserControllerTest extends TestCase
{
    private $repository;
    private SystemUserController $controller;

    protected function setUp(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|SystemUserRepository */
        $this->repository = $this->getMockBuilder(SystemUserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new SystemUserController($this->repository);
    }

    public function testIndex(): void
    {
        $mockResponse = new BackendIntegrationServiceResponse(['user1', 'user2'], 200);
        $this->repository->expects($this->once())->method('getAll')->willReturn($mockResponse);

        $response = $this->controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testStoreValidData(): void
    {
        $request = Request::create('/api/system/users', 'POST', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1], 201);
        $this->repository->method('create')->willReturn($mockResponse);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testStoreInvalidData(): void
    {
        $this->expectException(ValidationException::class);

        $request = Request::create('/api/system/users', 'POST', [
            'username' => '',
            'email' => 'invalid-email',
        ]);

        $this->controller->store($request);
    }

    public function testShow(): void
    {
        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'username' => 'testuser'], 200);
        $this->repository->method('getById')->with('1')->willReturn($mockResponse);

        $response = $this->controller->show('1');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testUpdate(): void
    {
        $request = Request::create('/api/system/users/1', 'PUT', [
            'email' => 'updated@example.com',
        ]);

        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'email' => 'updated@example.com'], 200);
        $this->repository->method('update')->with('1', $request->all())->willReturn($mockResponse);

        $response = $this->controller->update($request, '1');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testDestroy(): void
    {
        $mockResponse = new BackendIntegrationServiceResponse(null, 204);
        $this->repository->method('delete')->with('1')->willReturn($mockResponse);

        $response = $this->controller->destroy('1');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testActivate(): void
    {
        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'is_active' => true], 200);
        $this->repository->method('activate')->with('1')->willReturn($mockResponse);

        $response = $this->controller->activate('1');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testDeactivate(): void
    {
        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'is_active' => false], 200);
        $this->repository->method('deactivate')->with('1')->willReturn($mockResponse);

        $response = $this->controller->deactivate('1');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testConfirm(): void
    {
        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'is_confirmed' => true], 200);
        $this->repository->method('confirm')->with('1')->willReturn($mockResponse);

        $response = $this->controller->confirm('1');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testUnconfirm(): void
    {
        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'is_confirmed' => false], 200);
        $this->repository->method('unconfirm')->with('1')->willReturn($mockResponse);

        $response = $this->controller->unconfirm('1');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }
}
