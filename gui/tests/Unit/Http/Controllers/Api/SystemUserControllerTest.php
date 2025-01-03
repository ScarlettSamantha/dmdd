<?php

declare(strict_types=1);

namespace Tests\Unit;

use Error;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Scarlett\DMDD\GUI\Http\Controllers\Api\SystemUserController;
use Scarlett\DMDD\GUI\Repositories\SystemUserRepository;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;
use Illuminate\Support\MessageBag;

class SystemUserControllerTest extends TestCase
{
    private MockObject|SystemUserRepository $repository;
    private MockObject|ValidationFactory $validationFactory;
    private SystemUserController $controller;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SystemUserRepository::class);
        $this->validationFactory = $this->createMock(ValidationFactory::class);
        $this->controller = new SystemUserController($this->repository, $this->validationFactory);
    }

    public function testStoreValidData(): void
    {
        $requestData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $request = Request::create('/api/system/users', 'POST', $requestData);

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
            ->with($this->equalTo($requestData))
            ->willReturn($mockResponse);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testStoreInvalidData(): void
    {
        $request = Request::create('/api/system/users', 'POST', [
            'username' => '',
            'email' => 'invalid-email',
        ]);

        $mockMessageBag = $this->createMock(MessageBag::class);
        $mockMessageBag
            ->method('all')
            ->willReturn(['The username field is required.']);

        $mockValidator = $this->createMock(Validator::class);
        $mockValidator
            ->method('errors')
            ->willReturn($mockMessageBag);

        $mockValidator
            ->method('validate')
            ->willThrowException(new ValidationException($mockValidator));

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), $this->isType('array'))
            ->willReturn($mockValidator);

        $this->expectException(Error::class);

        try {
            $this->controller->store($request);
        } catch (ValidationException $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertEquals(['The username field is required.'], $e->errors());
        }
    }

    public function testUpdateValidData(): void
    {
        $userId = '1';
        $requestData = [
            'email' => 'updated@example.com',
        ];

        $request = Request::create('/api/system/users/1', 'PUT', $requestData);

        $mockValidator = $this->createMock(Validator::class);
        $mockValidator->expects($this->once())->method('validate')->willReturn($requestData);

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), $this->isType('array'))
            ->willReturn($mockValidator);

        $mockResponse = new BackendIntegrationServiceResponse(['id' => 1, 'email' => 'updated@example.com'], 200);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($this->equalTo($userId), $this->equalTo($requestData))
            ->willReturn($mockResponse);

        $response = $this->controller->update($request, $userId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($mockResponse->toArray(), $response->getData(true));
    }

    public function testUpdateInvalidData(): void
    {
        $userId = '1';
        $request = Request::create('/api/system/users/1', 'PUT', [
            'email' => 'invalid-email',
        ]);

        $mockMessageBag = $this->createMock(MessageBag::class);
        $mockMessageBag
            ->method('all')
            ->willReturn(['The email must be a valid email address.']);

        $mockValidator = $this->createMock(Validator::class);
        $mockValidator
            ->method('errors')
            ->willReturn($mockMessageBag);

        $mockValidator
            ->method('validate')
            ->willThrowException(new ValidationException($mockValidator));

        $this->validationFactory
            ->expects($this->once())
            ->method('make')
            ->with($request->all(), $this->isType('array'))
            ->willReturn($mockValidator);

        $this->expectException(Error::class);

        try {
            $this->controller->update($request, $userId);
        } catch (ValidationException $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertEquals(['The email must be a valid email address.'], $e->errors());
        }
    }
}
