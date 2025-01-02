<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Models;

use JsonSerializable;

/**
 * Class Library
 *
 * Represents a library entity retrieved from or sent to the dmdd-core API.
 */
class Library implements JsonSerializable
{
    public string $id;
    public string $name;
    public string $description;
    public bool $isPublic;
    public string $ownerId;

    /**
     * Constructor to initialize a Library.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'] ?? '';
        $this->name = $attributes['name'] ?? '';
        $this->description = $attributes['description'] ?? '';
        $this->isPublic = (bool) ($attributes['isPublic'] ?? true);
        $this->ownerId = $attributes['ownerId'] ?? '';
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
            'name' => $this->name,
            'description' => $this->description,
            'isPublic' => $this->isPublic,
            'ownerId' => $this->ownerId,
        ];
    }

    /**
     * Create a Library instance from an array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Convert a Library instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
