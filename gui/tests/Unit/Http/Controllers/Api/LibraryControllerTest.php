<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Scarlett\DMDD\GUI\Http\Controllers\Api\LibraryController;
use Scarlett\DMDD\GUI\Repositories\LibraryRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\Validator;

class LibraryControllerTest extends TestCase
{
    private MockObject|LibraryRepository $repository;
    private LibraryController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LibraryRepository::class);
        $validationFactory = $this->createMock(ValidationFactory::class);
        $this->controller = new LibraryController($this->repository, $validationFactory);
    }

    public function testIndex(): void
    {
        $mockResponse = ['library1', 'library2'];

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($mockResponse);

        $response = $this->controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse, $response->getData(true));
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

        $mockResponse = ['id' => 1];

        Validator::shouldReceive('make')
            ->once()
            ->with($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'isPublic' => 'required|boolean',
                'ownerId' => 'required|string|uuid',
            ])
            ->andReturn(
                \Mockery::mock()
                    ->shouldReceive('fails')
                    ->andReturn(false)
                    ->andReturn($validatedData)
                    ->getMock()
            );

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with($validatedData)
            ->willReturn($mockResponse);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($mockResponse, $response->getData(true));
    }

    public function testStoreInvalidData(): void
    {
        $this->expectException(ValidationException::class);

        $request = Request::create('/api/libraries', 'POST', [
            'name' => '',
            'isPublic' => 'not-a-boolean',
        ]);

        Validator::shouldReceive('make')
            ->once()
            ->with($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'isPublic' => 'required|boolean',
                'ownerId' => 'required|string|uuid',
            ])
            ->andReturn(
                \Mockery::mock()
                    ->shouldReceive('fails')
                    ->andReturn(true)
                    ->andReturn(new \Illuminate\Support\MessageBag([
                        'name' => 'The name field is required.',
                        'isPublic' => 'The isPublic field must be true or false.',
                    ]))
                    ->getMock()
            );

        $this->controller->store($request);
    }

    public function testShow(): void
    {
        $libraryId = '1';
        $mockResponse = ['id' => 1, 'name' => 'Test Library'];

        $this->repository
            ->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($libraryId))
            ->willReturn($mockResponse);

        $response = $this->controller->show($libraryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse, $response->getData(true));
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

        $mockResponse = ['id' => 1, 'name' => 'Updated Library'];

        Validator::shouldReceive('make')
            ->once()
            ->with($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'isPublic' => 'sometimes|boolean',
                'ownerId' => 'sometimes|string|uuid',
            ])
            ->andReturn(
                \Mockery::mock()
                    ->shouldReceive('fails')
                    ->andReturn(false)
                    ->andReturn($validatedData)
                    ->getMock()
            );

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($this->equalTo($libraryId), $this->equalTo($validatedData))
            ->willReturn($mockResponse);

        $response = $this->controller->update($request, $libraryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse, $response->getData(true));
    }

    public function testDestroy(): void
    {
        $libraryId = '1';

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($libraryId));

        $response = $this->controller->destroy($libraryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
