<?php

namespace App\User\Application\Command;

final readonly class ValidatePinCommand
{
    private function __construct(
        public string $userUuid,
        public string $pin,
    ) {}

    public static function create(string $userUuid, string $pin): self
    {
        return new self(
            userUuid: $userUuid,
            pin:      $pin,
        );
    }
}
