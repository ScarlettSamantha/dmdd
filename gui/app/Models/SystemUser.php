<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Models;

use JsonSerializable;

/**
 * Class SystemUser
 *
 * Represents a user entity retrieved from or sent to the dmdd-core API.
 */
class SystemUser implements JsonSerializable
{
    public string $id;
    public string $username;
    public string $email;
    public ?string $firstName;
    public ?string $lastName;
    public bool $isActive;
    public bool $isConfirmed;
    public bool $isAdmin;

    /**
     * Constructor to initialize a SystemUser.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'] ?? '';
        $this->username = $attributes['username'] ?? '';
        $this->email = $attributes['email'] ?? '';
        $this->firstName = $attributes['firstName'] ?? null;
        $this->lastName = $attributes['lastName'] ?? null;
        $this->isActive = (bool) ($attributes['isActive'] ?? false);
        $this->isConfirmed = (bool) ($attributes['isConfirmed'] ?? false);
        $this->isAdmin = (bool) ($attributes['isAdmin'] ?? false);
    }

    /**
     * Serialize the model for JSON responses.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'isActive' => $this->isActive,
            'isConfirmed' => $this->isConfirmed,
            'isAdmin' => $this->isAdmin,
        ];
    }

    /**
     * Create a SystemUser instance from an array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Convert a SystemUser instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}