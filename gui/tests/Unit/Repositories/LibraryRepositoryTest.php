<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Tests\Repositories;

use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Scarlett\DMDD\GUI\Models\Library;
use Scarlett\DMDD\GUI\Repositories\LibraryRepository;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

class LibraryRepositoryTest extends TestCase
{
    private BackendIntegrationService|MockObject $backendIntegrationService;
    private LibraryRepository $repository;

    protected function setUp(): void
    {
        $this->backendIntegrationService = $this->createMock(BackendIntegrationService::class);
        $this->repository = new LibraryRepository($this->backendIntegrationService);
    }

    public function testGetByIdReturnsSuccessResponse(): void
    {
        $libraryId = 'library123';
        $mockLibraryData = ['id' => $libraryId, 'name' => 'Test Library'];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getLibrary')
            ->with($libraryId)
            ->willReturn($mockLibraryData);

        $response = $this->repository->getById($libraryId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertInstanceOf(Library::class, $response->data);
        $this->assertSame($libraryId, $response->data->id);
    }

    public function testGetByIdReturnsErrorResponse(): void
    {
        $libraryId = 'library123';

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getLibrary')
            ->with($libraryId)
            ->willThrowException(new \Exception('Library not found'));

        $response = $this->repository->getById($libraryId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(404, $response->status_code);
        $this->assertSame('Library not found', $response->message);
    }

    public function testGetAllReturnsSuccessResponse(): void
    {
        $mockLibrariesData = [
            ['id' => 'library1', 'name' => 'Library 1'],
            ['id' => 'library2', 'name' => 'Library 2']
        ];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('getLibraries')
            ->willReturn($mockLibrariesData);

        $response = $this->repository->getAll();

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertCount(2, $response->data);
        $this->assertInstanceOf(Library::class, $response->data[0]);
    }

    public function testCreateReturnsSuccessResponse(): void
    {
        $libraryData = ['name' => 'New Library'];
        $mockCreatedLibrary = ['id' => 'library789', 'name' => 'New Library'];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('createLibrary')
            ->with($libraryData)
            ->willReturn($mockCreatedLibrary);

        $response = $this->repository->create($libraryData);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(201, $response->status_code);
        $this->assertInstanceOf(Library::class, $response->data);
        $this->assertSame('library789', $response->data->id);
    }

    public function testUpdateReturnsSuccessResponse(): void
    {
        $libraryId = 'library123';
        $updateData = ['name' => 'Updated Library'];
        $mockUpdatedLibrary = ['id' => $libraryId, 'name' => 'Updated Library'];

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('updateLibrary')
            ->with($libraryId, $updateData)
            ->willReturn($mockUpdatedLibrary);

        $response = $this->repository->update($libraryId, $updateData);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(200, $response->status_code);
        $this->assertInstanceOf(Library::class, $response->data);
        $this->assertSame('Updated Library', $response->data->name);
    }

    public function testDeleteReturnsSuccessResponse(): void
    {
        $libraryId = 'library123';

        $this->backendIntegrationService
            ->expects($this->once())
            ->method('deleteLibrary')
            ->with($libraryId);

        $response = $this->repository->delete($libraryId);

        $this->assertInstanceOf(BackendIntegrationServiceResponse::class, $response);
        $this->assertSame(204, $response->status_code);
        $this->assertNull($response->data);
    }
}
