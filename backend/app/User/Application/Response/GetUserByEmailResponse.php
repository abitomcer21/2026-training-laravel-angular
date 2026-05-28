<?php

namespace App\User\Application\Response;

use App\User\Domain\Entity\User;

final readonly class GetUserByEmailResponse
{
    private function __construct(
        private string $uuid,
        private string $name,
        private string $email,
        private string $role,
        private ?string $imageSrc,
        private int $restaurantId,
    ) {}

    public static function create(User $user): self
    {
        return new self(
            uuid:         $user->id()->value(),
            name:         $user->name()->value(),
            email:        $user->email()->value(),
            role:         $user->role()->value(),
            imageSrc:     $user->imageSrc(),
            restaurantId: $user->restaurantId(),
        );
    }

    public function toArray(): array
    {
        return [
            'uuid'          => $this->uuid,
            'name'          => $this->name,
            'email'         => $this->email,
            'role'          => $this->role,
            'image_src'     => $this->imageSrc,
            'restaurant_id' => $this->restaurantId,
        ];
    }
}
