<?php

namespace App\User\Domain\Exceptions;

class UserNotFoundException extends \DomainException
{
    private string $userId;

    public function __construct(string $userId)
    {
        parent::__construct(
            sprintf('User with ID %s not found', $userId),
            404,
        );

        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
