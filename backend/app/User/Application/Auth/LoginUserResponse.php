<?php

namespace App\User\Application\Auth;

use App\User\Domain\Entity\User;

class LoginUserResponse
{
    public function __construct(
        public string $token,
        public string $name,
        public string $email,
        public string $role,
        public ?string $imageSrc,
        public int $restaurantId,
        public array $restaurants,
        public bool $requiresRestaurantSelection,
    ) {}

    public static function create(User $user, string $token, array $restaurants): self
    {
        return new self(
            token: $token,
            name: $user->name(),
            email: $user->email()->value(),
            role: $user->role()->value(),
            imageSrc: $user->imageSrc(),
            restaurantId: $user->restaurantId(),
            restaurants: array_map(
                fn ($restaurant): array => [
                    'uuid' => $restaurant->id()->value(),
                    'name' => $restaurant->name(),
                    'legal_name' => $restaurant->legalName(),
                ],
                $restaurants,
            ),
            requiresRestaurantSelection: $user->role()->isAdmin() && count($restaurants) > 1,
        );
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'token_type' => 'Bearer',
            'requires_restaurant_selection' => $this->requiresRestaurantSelection,
            'restaurants' => $this->restaurants,
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