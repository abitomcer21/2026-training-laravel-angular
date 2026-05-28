<?php

namespace App\User\Application\Handler;

use App\User\Application\Command\LogoutUserCommand;
use App\User\Domain\Interfaces\TokenRevokerInterface;

class LogoutUserHandler
{
    public function __construct(
        private TokenRevokerInterface $tokenRevoker,
    ) {}

    public function __invoke(LogoutUserCommand $command): void
    {
        $this->tokenRevoker->revoke($command->token);
    }
}
