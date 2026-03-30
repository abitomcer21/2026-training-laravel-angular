<?php

namespace App\User\Application\GetUserById;

use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Role;


final readonly class GetUserByIdResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        private Role $role,
        public ?string $imageSrc,
        public ?int $restaurantId,
        public string $pin,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}

    public static function create(User $user): self
    {
        return new self(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email()->value(),
            role: $user->role(),
            imageSrc: $user->imageSrc(),
            restaurantId: $user->restaurantId(),
            pin: $user->pin(),
            createdAt: $user->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $user->updatedAt()->format(\DateTimeInterface::ATOM),
            deletedAt: $user->deletedAt() ? $user->deletedAt()->format(\DateTimeInterface::ATOM) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value(),
            'pin' => $this->pin,
            'image_src' => $this->imageSrc,
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }
}