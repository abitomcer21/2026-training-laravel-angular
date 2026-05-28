<?php

namespace App\User\Application\Command;

final readonly class LogoutUserCommand
{
    private function __construct(
        public string $token,
    ) {}

    public static function create(string $token): self
    {
        return new self(
            token: $token,
        );
    }
}
