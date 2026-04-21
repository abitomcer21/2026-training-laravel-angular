<?php

namespace App\User\Application\GetAllUsers;

use App\User\Domain\Entity\User;

final readonly class GetAllUsersItem
{
    public function __construct(
        public int $numericId,
        public User $user,
    ) {}
}