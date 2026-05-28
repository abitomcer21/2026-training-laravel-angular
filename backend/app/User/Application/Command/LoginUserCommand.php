<?php

namespace App\User\Application\Command;

final readonly class LoginUserCommand
{
    private function __construct(
        public string $email,
        public string $plainPassword,
    ) {}

    public static function create(string $email, string $plainPassword): self
    {
        return new self(
            email:         $email,
            plainPassword: $plainPassword,
        );
    }
}
