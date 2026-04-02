<?php

namespace App\User\Application\Auth;

use App\User\Domain\Interfaces\TokenRevokerInterface;

class LogoutUser
{
    public function __construct(private TokenRevokerInterface $tokenRevoker) {}

    public function __invoke(
        string $token
    ) {
        $this->tokenRevoker->revoke($token);
    }
}
