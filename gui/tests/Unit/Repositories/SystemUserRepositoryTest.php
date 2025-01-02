<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Tests\Repositories;

use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Scarlett\DMDD\GUI\Models\SystemUser;
use Scarlett\DMDD\GUI\Repositories\SystemUserRepository;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

class SystemUserRepositoryTest extends TestCase
{
    private BackendIntegrationService|MockObject $backendIntegrationService;
    private SystemUserRepository $repository;

    protected function setUp(): void
    {
        $this->backendIntegrationService = $this->createMock(BackendIntegrationService::class);
        $this->repository = new SystemUserRepository($this->backendIntegrationService);
    }

    public function testGetByIdReturnsSuccessResponse(): void
    {
        $userId = 'user123';
        $mockUserData = ['id' => $userId, 'name' => 'Test User'];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getSystemUser')
            ->with($userId)
            ->willReturn($mockUserData);

        $response = $this->repository->getById($userId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertInstanceOf(SystemUser::class, $response->data);
        $this->assertSame($userId, $response->data->id);
    }

    public function testGetByIdReturnsErrorResponse(): void
    {
        $userId = 'user123';

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getSystemUser')
            ->with($userId)
            ->willThrowException(new \Exception('User not found'));

        $response = $this->repository->getById($userId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(404, $response->status_code);
        $this->assertSame('User not found', $response->message);
    }

    public function testGetAllReturnsSuccessResponse(): void
    {
        $mockUsersData = [
            ['id' => 'user1', 'name' => 'User 1'],
            ['id' => 'user2', 'name' => 'User 2']
        ];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getSystemUsers')
            ->willReturn($mockUsersData);

        $response = $this->repository->getAll();

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertCount(2, $response->data);
        $this->assertInstanceOf(SystemUser::class, $response->data[0]);
    }

    public function testCreateReturnsSuccessResponse(): void
    {
        $userData = ['name' => 'New User'];
        $mockCreatedUser = ['id' => 'user789', 'name' => 'New User'];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('createSystemUser')
            ->with($userData)
            ->willReturn($mockCreatedUser);

        $response = $this->repository->create($userData);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(201, $response->status_code);
        $this->assertInstanceOf(SystemUser::class, $response->data);
        $this->assertSame('user789', $response->data->id);
    }

    public function testDeleteReturnsSuccessResponse(): void
    {
        $userId = 'user123';

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('deleteSystemUser')
            ->with($userId);

        $response = $this->repository->delete($userId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(204, $response->status_code);
        $this->assertNull($response->data);
    }

    public function testActivateReturnsSuccessResponse(): void
    {
        $userId = 'user123';
        $mockActivatedUser = ['id' => $userId, 'is_active' => true];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('activateSystemUser')
            ->with($userId)
            ->willReturn($mockActivatedUser);

        $response = $this->repository->activate($userId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertTrue($response->data->is_active);
    }

    public function testDeactivateReturnsSuccessResponse(): void
    {
        $userId = 'user123';
        $mockDeactivatedUser = ['id' => $userId, 'is_active' => false];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('deactivateSystemUser')
            ->with($userId)
            ->willReturn($mockDeactivatedUser);

        $response = $this->repository->deactivate($userId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertFalse($response->data->is_active);
    }

    public function testConfirmReturnsSuccessResponse(): void
    {
        $userId = 'user123';
        $mockConfirmedUser = ['id' => $userId, 'is_confirmed' => true];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('confirmSystemUser')
            ->with($userId)
            ->willReturn($mockConfirmedUser);

        $response = $this->repository->confirm($userId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertTrue($response->data->is_confirmed);
    }

    public function testUnconfirmReturnsSuccessResponse(): void
    {
        $userId = 'user123';
        $mockUnconfirmedUser = ['id' => $userId, 'is_confirmed' => false];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('unconfirmSystemUser')
            ->with($userId)
            ->willReturn($mockUnconfirmedUser);

        $response = $this->repository->unconfirm($userId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertFalse($response->data->is_confirmed);
    }
}
