<?php

namespace App\User\Application\Handler;

use App\User\Application\Query\GetMeQuery;
use App\User\Application\Response\GetMeResponse;
use App\User\Domain\Exceptions\UserNotFoundException;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetMeHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(GetMeQuery $query): GetMeResponse
    {
        $user = $this->userRepository->findById($query->uuid);

        if ($user === null) {
            throw new UserNotFoundException($query->uuid);
        }

        return GetMeResponse::create($user);
    }
}
