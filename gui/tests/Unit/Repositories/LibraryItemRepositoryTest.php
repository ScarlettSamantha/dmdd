<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Tests\Repositories;

use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Scarlett\DMDD\GUI\Models\LibraryItem;
use Scarlett\DMDD\GUI\Repositories\LibraryItemRepository;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

class LibraryItemRepositoryTest extends TestCase
{
    private BackendIntegrationService|MockObject $backendIntegrationService;
    private LibraryItemRepository $repository;

    protected function setUp(): void
    {
        $this->backendIntegrationService = $this->createMock(BackendIntegrationService::class);
        $this->repository = new LibraryItemRepository($this->backendIntegrationService);
    }

    public function testGetByIdReturnsSuccessResponse(): void
    {
        $libraryId = 'library123';
        $itemId = 'item456';
        $mockItemData = ['id' => $itemId, 'name' => 'Test Item'];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getLibraryItem')
            ->with($libraryId, $itemId)
            ->willReturn($mockItemData);

        $response = $this->repository->getById($libraryId, $itemId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertInstanceOf(LibraryItem::class, $response->data);
        $this->assertSame($itemId, $response->data->id);
    }

    public function testGetByIdReturnsErrorResponse(): void
    {
        $libraryId = 'library123';
        $itemId = 'item456';

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getLibraryItem')
            ->with($libraryId, $itemId)
            ->willThrowException(new \Exception('Item not found'));

        $response = $this->repository->getById($libraryId, $itemId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(404, $response->status_code);
        $this->assertSame('Item not found', $response->message);
    }

    public function testGetAllReturnsSuccessResponse(): void
    {
        $libraryId = 'library123';
        $mockItemsData = [
            ['id' => 'item1', 'name' => 'Item 1'],
            ['id' => 'item2', 'name' => 'Item 2']
        ];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getLibraryItems')
            ->with($libraryId)
            ->willReturn($mockItemsData);

        $response = $this->repository->getAll($libraryId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertCount(2, $response->data);
        $this->assertInstanceOf(LibraryItem::class, $response->data[0]);
    }

    public function testCreateReturnsSuccessResponse(): void
    {
        $libraryId = 'library123';
        $itemData = ['name' => 'New Item'];
        $mockCreatedItem = ['id' => 'item789', 'name' => 'New Item'];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('createLibraryItem')
            ->with($libraryId, $itemData)
            ->willReturn($mockCreatedItem);

        $response = $this->repository->create($libraryId, $itemData);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(201, $response->status_code);
        $this->assertInstanceOf(LibraryItem::class, $response->data);
        $this->assertSame('item789', $response->data->id);
    }

    public function testDeleteReturnsSuccessResponse(): void
    {
        $libraryId = 'library123';
        $itemId = 'item456';

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('deleteLibraryItem')
            ->with($libraryId, $itemId);

        $response = $this->repository->delete($libraryId, $itemId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(204, $response->status_code);
        $this->assertNull($response->data);
    }
}
