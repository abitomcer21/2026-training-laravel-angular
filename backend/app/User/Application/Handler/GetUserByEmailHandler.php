<?php

namespace App\User\Application\Handler;

use App\User\Application\Query\GetUserByEmailQuery;
use App\User\Application\Response\GetUserByEmailResponse;
use App\User\Domain\Exceptions\UserNotFoundException;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetUserByEmailHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(GetUserByEmailQuery $query): GetUserByEmailResponse
    {
        $user = $this->userRepository->findByEmail($query->email);

        if ($user === null) {
            throw new UserNotFoundException($query->email);
        }

        return GetUserByEmailResponse::create($user);
    }
}
