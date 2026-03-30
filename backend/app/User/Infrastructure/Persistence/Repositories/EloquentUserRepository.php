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
        $model = $this->model->newQuery()->firstOrNew(['uuid' => $user->id()->value()]);

        if (!$model->exists) {
            $model->created_at = $user->createdAt()->value();
        }

        $model->fill([
            'role' => $user->role()->value(),
            'image_src' => $user->imageSrc(),
            'restaurant_id' => $user->restaurantId(),
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'password' => $user->passwordHash(),
            'pin' => $user->pin(),
        ]);

        $model->updated_at = $user->updatedAt()->value();
        $model->deleted_at = $user->deletedAt()?->value();

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
            $model->created_at->toDateTimeImmutable(),
            $model->updated_at->toDateTimeImmutable(),
            $model->role,
            $model->image_src,
            $model->restaurant_id,
            $model->pin,
            $model->deleted_at?->toDateTimeImmutable(),
        );
    }

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn (EloquentUser $model) => User::fromPersistence(
                $model->uuid,
                $model->name,
                $model->email,
                $model->password,
                $model->created_at->toDateTimeImmutable(),
                $model->updated_at->toDateTimeImmutable(),
                $model->role,
                $model->image_src,
                $model->restaurant_id,
                $model->pin,
                $model->deleted_at?->toDateTimeImmutable(),
            )
        )->toArray();
    }
}
