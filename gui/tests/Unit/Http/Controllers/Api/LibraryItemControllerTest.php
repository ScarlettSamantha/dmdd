<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Scarlett\DMDD\GUI\Http\Controllers\Api\LibraryItemController;
use Scarlett\DMDD\GUI\Repositories\LibraryItemRepository;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

class LibraryItemControllerTest extends TestCase
{
    private MockObject|LibraryItemRepository $repository;
    private MockObject|ValidationFactory $validationFactory;
    private LibraryItemController $controller;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LibraryItemRepository::class);
        $this->validationFactory = $this->createMock(ValidationFactory::class);
        $this->controller = new LibraryItemController($this->repository, $this->validationFactory);
    }

    public function testStoreValidData(): void
    {
        $libraryId = '1';
        $requestData = [
            'name' => 'Test Item',
            'isPublic' => true,
            'ownerId' => '123e4567-e89b-12d3-a456-426614174000',
            'mimeType' => 'application/pdf',
            'fileSize' => 1024,
            'filePath' => '/path/to/file',
        ];

        $request = Request::create('/api/libraries/1/items', 'POST', $requestData);
        $mockValidator = $this->createMock(Validator::class);
        $mockValidator->expects($this->once())->method('validate')->willReturn($requestData);

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), $this->isType('array'))
            ->willReturn($mockValidator);

        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1], 201);

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($libraryId), $this->equalTo($requestData))
            ->willReturn($mockResponse);

        $response = $this->controller->store($request, $libraryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testStoreInvalidData(): void
    {
        $libraryId = '1';
        $request = Request::create('/api/libraries/1/items', 'POST', [
            'name' => '',
            'isPublic' => 'not-a-boolean',
        ]);

        $mockValidator = $this->createMock(Validator::class);
        $mockValidator
            ->method('validate')
            ->willThrowException(new ValidationException($mockValidator));
        $mockValidator
            ->method('errors')
            ->willReturn(collect(['name' => ['The name field is required.']]));

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), $this->isType('array'))
            ->willReturn($mockValidator);

        $this->expectException(ValidationException::class);
        $this->controller->store($request, $libraryId);
    }

    public function testUpdateValidData(): void
    {
        $libraryId = '1';
        $libraryItemId = '2';
        $requestData = ['name' => 'Updated Name'];
        $request = Request::create('/api/libraries/1/items/2', 'PUT', $requestData);

        $mockValidator = $this->createMock(Validator::class);
        $mockValidator->expects($this->once())->method('validate')->willReturn($requestData);

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), $this->isType('array'))
            ->willReturn($mockValidator);

        $mockResponse = new BackendIntegrationServiceResponse(['id' => 2, 'name' => 'Updated Name'], 200);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($this->equalTo($libraryId), $this->equalTo($libraryItemId), $this->equalTo($requestData))
            ->willReturn($mockResponse);

        $response = $this->controller->update($request, $libraryId, $libraryItemId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testUpdateInvalidData(): void
    {
        $libraryId = '1';
        $libraryItemId = '2';
        $request = Request::create('/api/libraries/1/items/2', 'PUT', ['name' => '']);

        $mockValidator = $this->createMock(Validator::class);
        $mockValidator
            ->method('validate')
            ->willThrowException(new ValidationException($mockValidator));
        $mockValidator
            ->method('errors')
            ->willReturn(collect(['name' => ['The name field is required.']]));

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), $this->isType('array'))
            ->willReturn($mockValidator);

        $this->expectException(ValidationException::class);
        $this->controller->update($request, $libraryId, $libraryItemId);
    }
}
