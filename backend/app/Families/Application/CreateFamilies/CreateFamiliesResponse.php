<?php

namespace App\Families\Application\CreateFamilies;

use App\Families\Domain\Entity\Families;

final readonly class CreateFamiliesResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public bool $activo,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Families $families): self
    {
        return new self(
            id: $families->id()->value(),
            name: $families->name(),
            activo: $families->status()->isActive(),
            createdAt: $families->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $families->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'activo' => $this->activo,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
