<?php

namespace App\User\Infrastructure\Persistence\Repositories;

use App\User\Application\GetAllUsers\GetAllUsersItem;
use App\User\Application\GetAllUsers\GetAllUsersReadRepositoryInterface;
use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Infrastructure\Persistence\Models\EloquentUser;

class EloquentUserRepository implements UserRepositoryInterface, GetAllUsersReadRepositoryInterface
{
    public function __construct(
        private EloquentUser $model,
    ) {}

    public function save(User $user): void
    {
        $model = $this->model->newQuery()->firstOrNew(
            ['uuid' => $user->id()->value()]);

        if (! $model->exists) {
            $model->created_at = $user->createdAt()->value();
        }

        $model->fill([
            'role' => $user->role()->value(),
            'image_src' => $user->imageSrc(),
            'restaurant_id' => $user->restaurantId(),
            'name' => $user->name()->value(),
            'email' => $user->email()->value(),
            'password' => $user->passwordHash()->value(),
            'pin' => $user->pin()->value(),
        ]);

        $model->updated_at = $user->updatedAt()->value();
        $model->save();
    }

    public function findById(string $id): ?User
    {
        $model = $this->model->newQuery()->where('uuid', $id)->first();

        if ($model === null) {
            return null;
        }

        return User::fromPersistence(
            $model->uuid,
            $model->name,
            $model->email,
            $model->password,
            $model->role,
            $model->restaurant_id,
            $model->pin,
            $model->image_src,
            $model->created_at->toDateTimeImmutable(),
            $model->updated_at->toDateTimeImmutable(),
        );
    }

    public function findByEmail(string $email): ?User
    {
        $model = $this->model->newQuery()->where('email', $email)->first();

        if ($model === null) {
            return null;
        }

        return User::fromPersistence(
            $model->uuid,
            $model->name,
            $model->email,
            $model->password,
            $model->role,
            $model->restaurant_id,
            $model->pin,
            $model->image_src,
            $model->created_at->toDateTimeImmutable(),
            $model->updated_at->toDateTimeImmutable(),
        );
    }

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn (EloquentUser $model): User => User::fromPersistence(
                $model->uuid,
                $model->name,
                $model->email,
                $model->password,
                $model->role,
                $model->restaurant_id,
                $model->pin,
                $model->image_src,
                $model->created_at->toDateTimeImmutable(),
                $model->updated_at->toDateTimeImmutable(),
            ),
        )->toArray();
    }

    public function allWithNumericId(): array
    {
        return $this->model->newQuery()->get()->map(
            fn (EloquentUser $model): GetAllUsersItem => new GetAllUsersItem(
                $model->id,
                User::fromPersistence(
                    $model->uuid,
                    $model->name,
                    $model->email,
                    $model->password,
                    $model->role,
                    $model->restaurant_id,
                    $model->pin,
                    $model->image_src,
                    $model->created_at->toDateTimeImmutable(),
                    $model->updated_at->toDateTimeImmutable(),
                ),
            ),
        )->toArray();
    }

    public function allByRestaurantIdWithNumericId(int $restaurantId): array
    {
        return $this->model->newQuery()->where('restaurant_id', $restaurantId)->get()->map(
            fn (EloquentUser $model): GetAllUsersItem => new GetAllUsersItem(
                $model->id,
                User::fromPersistence(
                    $model->uuid,
                    $model->name,
                    $model->email,
                    $model->password,
                    $model->role,
                    $model->restaurant_id,
                    $model->pin,
                    $model->image_src,
                    $model->created_at->toDateTimeImmutable(),
                    $model->updated_at->toDateTimeImmutable(),
                ),
            ),
        )->toArray();
    }

    public function delete(string $id): void
    {
        $this->model->newQuery()->where('uuid', $id)->delete();
    }
}
