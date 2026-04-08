<?php

namespace App\User\Application\GetUserByEmail;

class GetUserByEmailResponse
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $email,
        public string $role,
        public ?string $imageSrc,
        public int $restaurantId,
    ) {}

    public static function create($user): self
    {
        return new self(
            uuid: $user->uuid,
            name: $user->name,
            email: $user->email,
            role: $user->role,
            imageSrc: $user->image_src,
            restaurantId: $user->restaurant_id,
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
            'restaurant_id' => $this->restaurantId,
        ];
    }
}