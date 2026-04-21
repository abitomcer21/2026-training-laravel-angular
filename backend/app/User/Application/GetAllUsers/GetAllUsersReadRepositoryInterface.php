<?php

namespace App\User\Application\GetAllUsers;

interface GetAllUsersReadRepositoryInterface
{
    public function allWithNumericId(): array;
}