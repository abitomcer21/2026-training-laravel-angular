<?php

namespace App\User\Application\GetAllUsers;

use App\User\Domain\Entity\User;

final readonly class GetAllUsersResponse
{
    public function __construct(
        public array $users,
        public int $total,
    ) {}

    public static function create(array $users): self
    {
        $usersData = array_map(
            static fn (User $user): array => [
                'id' => $user->id()->value(),
                'name' => $user->name()->value(),
                'email' => $user->email()->value(),
                'role' => $user->role()->value(),
                'pin' => $user->pin()->value(),
                'image_src' => $user->imageSrc(),
                'restaurant_id' => $user->restaurantId(),
                'created_at' => $user->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $user->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $users,
        );

        return new self(
            users: $usersData,
            total: count($usersData),
        );
    }

    public function toArray(): array
    {
        return [
            'users' => $this->users,
            'total' => $this->total,
        ];
    }
}
