<?php

namespace App\User\Application\Command;

use App\Shared\Domain\ValueObject\Uuid;

final readonly class DeleteUserCommand
{
    private function __construct(
        public Uuid $id,
    ) {}

    public static function create(string $id): self
    {
        return new self(
            id: Uuid::create($id),
        );
    }
}
