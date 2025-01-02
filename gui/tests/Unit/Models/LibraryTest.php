<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Scarlett\DMDD\GUI\Models\Library;

class LibraryTest extends TestCase
{
    public function testConstructorInitializesAttributes(): void
    {
        $attributes = [
            'id' => 'lib123',
            'name' => 'Test Library',
            'description' => 'A test library description.',
            'isPublic' => false,
            'ownerId' => 'owner456',
        ];

        $library = new Library($attributes);

        $this->assertEquals('lib123', $library->id);
        $this->assertEquals('Test Library', $library->name);
        $this->assertEquals('A test library description.', $library->description);
        $this->assertFalse($library->isPublic);
        $this->assertEquals('owner456', $library->ownerId);
    }

    public function testConstructorHandlesMissingAttributes(): void
    {
        $attributes = [
            'id' => 'lib123',
            'name' => 'Test Library',
        ];

        $library = new Library($attributes);

        $this->assertEquals('lib123', $library->id);
        $this->assertEquals('Test Library', $library->name);
        $this->assertEquals('', $library->description);
        $this->assertTrue($library->isPublic); // Default value for isPublic
        $this->assertEquals('', $library->ownerId);
    }

    public function testJsonSerialize(): void
    {
        $attributes = [
            'id' => 'lib123',
            'name' => 'Test Library',
            'description' => 'A test library description.',
            'isPublic' => true,
            'ownerId' => 'owner456',
        ];

        $library = new Library($attributes);

        $expectedJson = [
            'id' => 'lib123',
            'name' => 'Test Library',
            'description' => 'A test library description.',
            'isPublic' => true,
            'ownerId' => 'owner456',
        ];

        $this->assertEquals($expectedJson, $library->jsonSerialize());
    }

    public function testToArray(): void
    {
        $attributes = [
            'id' => 'lib123',
            'name' => 'Test Library',
            'description' => 'A test library description.',
            'isPublic' => true,
            'ownerId' => 'owner456',
        ];

        $library = new Library($attributes);

        $expectedArray = [
            'id' => 'lib123',
            'name' => 'Test Library',
            'description' => 'A test library description.',
            'isPublic' => true,
            'ownerId' => 'owner456',
        ];

        $this->assertEquals($expectedArray, $library->toArray());
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'lib789',
            'name' => 'Another Library',
            'description' => 'Another test library description.',
            'isPublic' => false,
            'ownerId' => 'owner123',
        ];

        $library = Library::fromArray($data);

        $this->assertEquals('lib789', $library->id);
        $this->assertEquals('Another Library', $library->name);
        $this->assertEquals('Another test library description.', $library->description);
        $this->assertFalse($library->isPublic);
        $this->assertEquals('owner123', $library->ownerId);
    }
}
