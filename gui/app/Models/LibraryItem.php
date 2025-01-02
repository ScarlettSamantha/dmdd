<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Models;

use JsonSerializable;

/**
 * Class LibraryItem
 *
 * Represents a library item entity retrieved from or sent to the dmdd-core API.
 */
class LibraryItem implements JsonSerializable
{
    public string $id;
    public string $name;
    public ?string $description;
    public bool $isPublic;
    public string $ownerId;
    public string $libraryId;
    public string $mimeType;
    public int $fileSize;
    public string $filePath;
    public ?string $rawData;
    public string $createdAt;
    public string $updatedAt;
    public ?string $deletedAt;

    /**
     * Constructor to initialize a LibraryItem.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'] ?? '';
        $this->name = $attributes['name'] ?? '';
        $this->description = $attributes['description'] ?? null;
        $this->isPublic = (bool) ($attributes['isPublic'] ?? true);
        $this->ownerId = $attributes['ownerId'] ?? '';
        $this->libraryId = $attributes['libraryId'] ?? '';
        $this->mimeType = $attributes['mimeType'] ?? '';
        $this->fileSize = (int) ($attributes['fileSize'] ?? 0);
        $this->filePath = $attributes['filePath'] ?? '';
        $this->rawData = $attributes['rawData'] ?? null;
        $this->createdAt = $attributes['createdAt'] ?? '';
        $this->updatedAt = $attributes['updatedAt'] ?? '';
        $this->deletedAt = $attributes['deletedAt'] ?? null;
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
            'libraryId' => $this->libraryId,
            'mimeType' => $this->mimeType,
            'fileSize' => $this->fileSize,
            'filePath' => $this->filePath,
            'rawData' => $this->rawData,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }

    /**
     * Create a LibraryItem instance from an array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Convert a LibraryItem instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
