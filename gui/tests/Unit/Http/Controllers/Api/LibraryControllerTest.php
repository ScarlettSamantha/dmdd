<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Scarlett\DMDD\GUI\Http\Controllers\Api\LibraryController;
use Scarlett\DMDD\GUI\Repositories\LibraryRepository;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

class LibraryControllerTest extends TestCase
{
    private MockObject|LibraryRepository $repository;
    private MockObject|ValidationFactory $validationFactory;
    private LibraryController $controller;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LibraryRepository::class);
        $this->validationFactory = $this->createMock(ValidationFactory::class);
        $this->controller = new LibraryController($this->repository, $this->validationFactory);
    }

    public function testIndex(): void
    {
        $mockResponse = new BackendIntegrationServiceResponse(['library1', 'library2'], 200);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($mockResponse);

        $response = $this->controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testStoreValidData(): void
    {
        $request = Request::create('/api/libraries', 'POST', [
            'name' => 'Test Library',
            'description' => 'A test library.',
            'isPublic' => true,
            'ownerId' => '123e4567-e89b-12d3-a456-426614174000',
        ]);

        $validatedData = [
            'name' => 'Test Library',
            'description' => 'A test library.',
            'isPublic' => true,
            'ownerId' => '123e4567-e89b-12d3-a456-426614174000',
        ];

        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1], 201);

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'isPublic' => 'required|boolean',
                'ownerId' => 'required|string|uuid',
            ])
            ->willReturn(new class($validatedData) {
                private array $data;
                public function __construct(array $data) { $this->data = $data; }
                public function validate() { return $this->data; }
            });

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with($validatedData)
            ->willReturn($mockResponse);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testStoreInvalidData(): void
    {
        $this->expectException(ValidationException::class);

        $request = Request::create('/api/libraries', 'POST', [
            'name' => '',
            'isPublic' => 'not-a-boolean',
        ]);

        $validatorMock = $this->createMock(\Illuminate\Contracts\Validation\Validator::class);
        $validatorMock->method('fails')->willReturn(true);
        $validatorMock->method('errors')->willReturn(collect(['error' => 'Invalid data']));

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'isPublic' => 'required|boolean',
                'ownerId' => 'required|string|uuid',
            ])
            ->willReturn($validatorMock);

        $this->controller->store($request);
    }

    public function testShow(): void
    {
        $libraryId = '1';
        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'name' => 'Test Library'], 200);

        $this->repository
            ->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($libraryId))
            ->willReturn($mockResponse);

        $response = $this->controller->show($libraryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testUpdate(): void
    {
        $libraryId = '1';
        $request = Request::create('/api/libraries/1', 'PUT', [
            'name' => 'Updated Library',
        ]);

        $validatedData = [
            'name' => 'Updated Library',
        ];

        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'name' => 'Updated Library'], 200);

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'isPublic' => 'sometimes|boolean',
                'ownerId' => 'sometimes|string|uuid',
            ])
            ->willReturn(new class($validatedData) {
                private array $data;
                public function __construct(array $data) { $this->data = $data; }
                public function validate() { return $this->data; }
            });

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($this->equalTo($libraryId), $this->equalTo($validatedData))
            ->willReturn($mockResponse);

        $response = $this->controller->update($request, $libraryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testDestroy(): void
    {
        $libraryId = '1';
        $mockResponse = new BackendIntegrationServiceResponse(null, 204);

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($libraryId))
            ->willReturn($mockResponse);

        $response = $this->controller->destroy($libraryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
