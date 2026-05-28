<?php

namespace App\User\Application\Handler;

use App\User\Application\Query\GetUserByIdQuery;
use App\User\Application\Response\GetUserByIdResponse;
use App\User\Domain\Exceptions\UserNotFoundException;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetUserByIdHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(GetUserByIdQuery $query): GetUserByIdResponse
    {
        $user = $this->userRepository->findById($query->id);

        if ($user === null) {
            throw new UserNotFoundException($query->id);
        }

        return GetUserByIdResponse::create($user);
    }
}
