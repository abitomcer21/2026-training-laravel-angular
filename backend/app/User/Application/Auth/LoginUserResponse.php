<?php

namespace App\User\Application\Auth;

use App\User\Domain\Entity\User;

final readonly class LoginUserResponse
{
    public function __construct(
        public string $token,
        public string $name,
        public string $email,
        public string $role,
        public ?string $imageSrc,
        public int $restaurantId,
    ) {}

    public static function create(User $user, string $token): self
    {
        return new self(
            token: $token,
            name: $user->name()->value(),
            email: $user->email()->value(),
            role: $user->role()->value(),
            imageSrc: $user->imageSrc(),
            restaurantId: $user->restaurantId(),
        );
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'image_src' => $this->imageSrc,
                'restaurant_id' => $this->restaurantId,
            ],
        ];
    }
}
