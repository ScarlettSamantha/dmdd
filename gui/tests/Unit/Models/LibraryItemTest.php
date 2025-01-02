<?php

namespace Tests\Unit;

use Tests\TestCase;
use Scarlett\DMDD\GUI\Models\LibraryItem;

class LibraryItemTest extends TestCase
{
    public function testConstructorInitializesAttributes(): void
    {
        $attributes = [
            'id' => 'item123',
            'name' => 'Test Item',
            'description' => 'A test library item.',
            'isPublic' => true,
            'ownerId' => 'owner456',
            'libraryId' => 'lib789',
            'mimeType' => 'application/pdf',
            'fileSize' => 12345,
            'filePath' => '/files/test.pdf',
            'rawData' => 'raw data example',
            'createdAt' => '2023-01-01 12:00:00',
            'updatedAt' => '2023-01-02 12:00:00',
            'deletedAt' => null,
        ];

        $item = new LibraryItem($attributes);

        $this->assertEquals('item123', $item->id);
        $this->assertEquals('Test Item', $item->name);
        $this->assertEquals('A test library item.', $item->description);
        $this->assertTrue($item->isPublic);
        $this->assertEquals('owner456', $item->ownerId);
        $this->assertEquals('lib789', $item->libraryId);
        $this->assertEquals('application/pdf', $item->mimeType);
        $this->assertEquals(12345, $item->fileSize);
        $this->assertEquals('/files/test.pdf', $item->filePath);
        $this->assertEquals('raw data example', $item->rawData);
        $this->assertEquals('2023-01-01 12:00:00', $item->createdAt);
        $this->assertEquals('2023-01-02 12:00:00', $item->updatedAt);
        $this->assertNull($item->deletedAt);
    }

    public function testConstructorHandlesMissingAttributes(): void
    {
        $attributes = [
            'id' => 'item123',
            'name' => 'Test Item',
        ];

        $item = new LibraryItem($attributes);

        $this->assertEquals('item123', $item->id);
        $this->assertEquals('Test Item', $item->name);
        $this->assertNull($item->description);
        $this->assertTrue($item->isPublic); // Default value
        $this->assertEquals('', $item->ownerId);
        $this->assertEquals('', $item->libraryId);
        $this->assertEquals('', $item->mimeType);
        $this->assertEquals(0, $item->fileSize);
        $this->assertEquals('', $item->filePath);
        $this->assertNull($item->rawData);
        $this->assertEquals('', $item->createdAt);
        $this->assertEquals('', $item->updatedAt);
        $this->assertNull($item->deletedAt);
    }

    public function testJsonSerialize(): void
    {
        $attributes = [
            'id' => 'item123',
            'name' => 'Test Item',
            'description' => 'A test library item.',
            'isPublic' => true,
            'ownerId' => 'owner456',
            'libraryId' => 'lib789',
            'mimeType' => 'application/pdf',
            'fileSize' => 12345,
            'filePath' => '/files/test.pdf',
            'rawData' => 'raw data example',
            'createdAt' => '2023-01-01 12:00:00',
            'updatedAt' => '2023-01-02 12:00:00',
            'deletedAt' => null,
        ];

        $item = new LibraryItem($attributes);

        $expectedJson = [
            'id' => 'item123',
            'name' => 'Test Item',
            'description' => 'A test library item.',
            'isPublic' => true,
            'ownerId' => 'owner456',
            'libraryId' => 'lib789',
            'mimeType' => 'application/pdf',
            'fileSize' => 12345,
            'filePath' => '/files/test.pdf',
            'rawData' => 'raw data example',
            'createdAt' => '2023-01-01 12:00:00',
            'updatedAt' => '2023-01-02 12:00:00',
            'deletedAt' => null,
        ];

        $this->assertEquals($expectedJson, $item->jsonSerialize());
    }

    public function testToArray(): void
    {
        $attributes = [
            'id' => 'item123',
            'name' => 'Test Item',
            'description' => 'A test library item.',
            'isPublic' => true,
            'ownerId' => 'owner456',
            'libraryId' => 'lib789',
            'mimeType' => 'application/pdf',
            'fileSize' => 12345,
            'filePath' => '/files/test.pdf',
            'rawData' => 'raw data example',
            'createdAt' => '2023-01-01 12:00:00',
            'updatedAt' => '2023-01-02 12:00:00',
            'deletedAt' => null,
        ];

        $item = new LibraryItem($attributes);

        $expectedArray = [
            'id' => 'item123',
            'name' => 'Test Item',
            'description' => 'A test library item.',
            'isPublic' => true,
            'ownerId' => 'owner456',
            'libraryId' => 'lib789',
            'mimeType' => 'application/pdf',
            'fileSize' => 12345,
            'filePath' => '/files/test.pdf',
            'rawData' => 'raw data example',
            'createdAt' => '2023-01-01 12:00:00',
            'updatedAt' => '2023-01-02 12:00:00',
            'deletedAt' => null,
        ];

        $this->assertEquals($expectedArray, $item->toArray());
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'item789',
            'name' => 'Another Item',
            'description' => 'Another test library item.',
            'isPublic' => false,
            'ownerId' => 'owner123',
            'libraryId' => 'lib456',
            'mimeType' => 'image/png',
            'fileSize' => 67890,
            'filePath' => '/files/image.png',
            'rawData' => null,
            'createdAt' => '2023-03-01 12:00:00',
            'updatedAt' => '2023-03-02 12:00:00',
            'deletedAt' => '2023-03-03 12:00:00',
        ];

        $item = LibraryItem::fromArray($data);

        $this->assertEquals('item789', $item->id);
        $this->assertEquals('Another Item', $item->name);
        $this->assertEquals('Another test library item.', $item->description);
        $this->assertFalse($item->isPublic);
        $this->assertEquals('owner123', $item->ownerId);
        $this->assertEquals('lib456', $item->libraryId);
        $this->assertEquals('image/png', $item->mimeType);
        $this->assertEquals(67890, $item->fileSize);
        $this->assertEquals('/files/image.png', $item->filePath);
        $this->assertNull($item->rawData);
        $this->assertEquals('2023-03-01 12:00:00', $item->createdAt);
        $this->assertEquals('2023-03-02 12:00:00', $item->updatedAt);
        $this->assertEquals('2023-03-03 12:00:00', $item->deletedAt);
    }
}
