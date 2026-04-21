<?php

namespace App\User\Application\GetAllUsers;

final readonly class GetAllUsersResponse
{
    public function __construct(
        public array $users,
        public int $total,
    ) {}

    /**
     * @param array<int, GetAllUsersItem> $users
     */
    public static function create(array $users): self
    {
        $usersData = array_map(
            static fn (GetAllUsersItem $item): array => [
                'id' => $item->numericId,
                'uuid' => $item->user->id()->value(),
                'name' => $item->user->name()->value(),
                'email' => $item->user->email()->value(),
                'role' => $item->user->role()->value(),
                'pin' => $item->user->pin()->value(),
                'image_src' => $item->user->imageSrc(),
                'restaurant_id' => $item->user->restaurantId(),
                'created_at' => $item->user->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $item->user->updatedAt()->format(\DateTimeInterface::ATOM),
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
