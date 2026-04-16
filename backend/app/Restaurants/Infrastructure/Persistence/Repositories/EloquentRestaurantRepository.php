<?php

namespace App\User\Infrastructure\Persistence\Repositories;

use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Infrastructure\Persistence\Models\EloquentUser;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EloquentUser $model,
    ) {}

    public function save(User $user): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['uuid' => $user->id()->value()],
            [
                'role' => $user->role()->value(),
                'image_src' => $user->imageSrc(),
                'restaurant_id' => $user->restaurantId(),
                'name' => $user->name()->value(),
                'email' => $user->email()->value(),
                'password' => $user->passwordHash()->value(),
                'pin' => $user->pin()->value(),
                'created_at' => $user->createdAt()->value(),
                'updated_at' => $user->updatedAt()->value(),
            ],
        );
    }

    public function findById(string $id): ?User
    {
        $eloquentUser = $this->model->newQuery()->where('uuid', $id)->first();

        if (! $eloquentUser) {
            return null;
        }

        return User::fromPersistence(
            $eloquentUser->uuid,
            $eloquentUser->name,
            $eloquentUser->email,
            $eloquentUser->password,
            $eloquentUser->role,
            $eloquentUser->restaurant_id,
            $eloquentUser->pin,
            $eloquentUser->image_src,
            $eloquentUser->created_at->toDateTimeImmutable(),
            $eloquentUser->updated_at->toDateTimeImmutable(),
        );
    }

    public function findByInternalId(int $id): ?User
    {
        $eloquentUser = $this->model->newQuery()->find($id);

        if (! $eloquentUser) {
            return null;
        }

        return User::fromPersistence(
            $eloquentUser->uuid,
            $eloquentUser->name,
            $eloquentUser->email,
            $eloquentUser->password,
            $eloquentUser->role,
            $eloquentUser->restaurant_id,
            $eloquentUser->pin,
            $eloquentUser->image_src,
            $eloquentUser->created_at->toDateTimeImmutable(),
            $eloquentUser->updated_at->toDateTimeImmutable(),
        );
    }

    public function findByEmail(string $email): ?User
    {
        $eloquentUser = $this->model->newQuery()->where('email', $email)->first();

        if (! $eloquentUser) {
            return null;
        }

        return User::fromPersistence(
            $eloquentUser->uuid,
            $eloquentUser->name,
            $eloquentUser->email,
            $eloquentUser->password,
            $eloquentUser->role,
            $eloquentUser->restaurant_id,
            $eloquentUser->pin,
            $eloquentUser->image_src,
            $eloquentUser->created_at->toDateTimeImmutable(),
            $eloquentUser->updated_at->toDateTimeImmutable(),
        );
    }

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn(EloquentUser $eloquentUser): User => User::fromPersistence(
                $eloquentUser->uuid,
                $eloquentUser->name,
                $eloquentUser->email,
                $eloquentUser->password,
                $eloquentUser->role,
                $eloquentUser->restaurant_id,
                $eloquentUser->pin,
                $eloquentUser->image_src,
                $eloquentUser->created_at->toDateTimeImmutable(),
                $eloquentUser->updated_at->toDateTimeImmutable(),
            ),
        )->toArray();
    }

    public function delete(string $id): void
    {
        $this->model->newQuery()->where('uuid', $id)->delete();
    }

    public function getInternalIdByUuid(string $uuid): ?int
    {
        $user = $this->model->newQuery()->where('uuid', $uuid)->first();

        return $user?->id;
    }
}