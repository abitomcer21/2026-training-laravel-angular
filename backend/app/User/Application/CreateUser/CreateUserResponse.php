<?php

namespace App\User\Application\CreateUser;

use App\User\Domain\Entity\User;

final readonly class CreateUserResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $role,
        public string $pin,
        public ?string $imageSrc,
        public int $restaurantId,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function create(User $user): self
    {
        return new self(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email()->value(),
            role: $user->role()->value(),
            pin: $user->pin(),
            imageSrc: $user->imageSrc(),
            restaurantId: $user->restaurantId(),
            createdAt: $user->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $user->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'pin' => $this->pin,
            'image_src' => $this->imageSrc,
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
