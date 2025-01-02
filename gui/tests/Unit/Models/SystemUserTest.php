<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Scarlett\DMDD\GUI\Models\SystemUser;

class SystemUserTest extends TestCase
{
    public function testConstructorInitializesAttributes(): void
    {
        $attributes = [
            'id' => '123',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'isActive' => true,
            'isConfirmed' => true,
            'isAdmin' => false,
        ];

        $user = new SystemUser($attributes);

        $this->assertEquals('123', $user->id);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('John', $user->firstName);
        $this->assertEquals('Doe', $user->lastName);
        $this->assertTrue($user->isActive);
        $this->assertTrue($user->isConfirmed);
        $this->assertFalse($user->isAdmin);
    }

    public function testConstructorHandlesMissingAttributes(): void
    {
        $attributes = [
            'id' => '123',
            'username' => 'testuser',
        ];

        $user = new SystemUser($attributes);

        $this->assertEquals('123', $user->id);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('', $user->email);
        $this->assertNull($user->firstName);
        $this->assertNull($user->lastName);
        $this->assertFalse($user->isActive);
        $this->assertFalse($user->isConfirmed);
        $this->assertFalse($user->isAdmin);
    }

    public function testJsonSerialize(): void
    {
        $attributes = [
            'id' => '123',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'isActive' => true,
            'isConfirmed' => false,
            'isAdmin' => true,
        ];

        $user = new SystemUser($attributes);

        $expectedJson = [
            'id' => '123',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'isActive' => true,
            'isConfirmed' => false,
            'isAdmin' => true,
        ];

        $this->assertEquals($expectedJson, $user->jsonSerialize());
    }

    public function testToArray(): void
    {
        $attributes = [
            'id' => '123',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'isActive' => true,
            'isConfirmed' => false,
            'isAdmin' => true,
        ];

        $user = new SystemUser($attributes);

        $expectedArray = [
            'id' => '123',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'isActive' => true,
            'isConfirmed' => false,
            'isAdmin' => true,
        ];

        $this->assertEquals($expectedArray, $user->toArray());
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => '456',
            'username' => 'newuser',
            'email' => 'new@example.com',
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'isActive' => true,
            'isConfirmed' => true,
            'isAdmin' => false,
        ];

        $user = SystemUser::fromArray($data);

        $this->assertEquals('456', $user->id);
        $this->assertEquals('newuser', $user->username);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('Jane', $user->firstName);
        $this->assertEquals('Smith', $user->lastName);
        $this->assertTrue($user->isActive);
        $this->assertTrue($user->isConfirmed);
        $this->assertFalse($user->isAdmin);
    }
}
