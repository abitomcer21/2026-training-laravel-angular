<?php

namespace App\User\Application\GetUserbyEmail;

use App\User\Application\GetUserById\GetUserByIdResponse;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class GetUserByEmail
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke (string $email): ?GetUserByEmailResponse
   {
        $user  = $this->userRepository->findByEmail($email);
        if(!$user){
            return null;
        }
        return GetUserByEmailResponse::create($user);
   } 
}
