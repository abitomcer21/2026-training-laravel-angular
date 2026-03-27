<?php

namespace App\User\Application\GetUsers;

use App\User\Domain\Entity\User;

final readonly class GetAllUsersResponse
{
    /**
     * @param array<int, array<string, mixed>> $users
     */
    public function __construct(
        public array $users,
        public int $total,
    ) {}

    /**
     * @param User[] $usersEntities
     */
    public static function create(array $usersEntities): self
    {
        $users = array_map(
            fn (User $user) => [
                'id' => $user->id()->value(),
                'name' => $user->name(),
                'email' => $user->email()->value(),
                'role' => $user->role(),
                'pin' => $user->pin(),
                'image_src' => $user->imageSrc(),
                'restaurant_id' => $user->restaurantId(),
                'created_at' => $user->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $user->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $usersEntities
        );

        return new self(
            users: $users,
            total: count($users),
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
