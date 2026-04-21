<?php

namespace App\User\Domain\Interfaces;

use App\User\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(string $id): ?User;

    public function all(): array;

    public function delete(string $id): void;

    public function findByEmail(string $email): ?User;

    public function allWithNumericId(): array;

    public function allByRestaurantIdWithNumericId(int $restaurantId): array;
}
