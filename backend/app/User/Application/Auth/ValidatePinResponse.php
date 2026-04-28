<?php

namespace App\User\Application\Auth;

use App\User\Domain\Entity\User;

final readonly class ValidatePinResponse
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $email,
        public string $role,
        public ?string $imageSrc,
    ) {}

    public static function create(User $user): self
    {
        return new self(
            uuid: $user->id()->value(),
            name: $user->name()->value(),
            email: $user->email()->value(),
            role: $user->role()->value(),
            imageSrc: $user->imageSrc(),
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'image_src' => $this->imageSrc,
        ];
    }
}
