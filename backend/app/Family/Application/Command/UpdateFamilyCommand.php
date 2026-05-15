<?php

namespace App\Family\Application\Command;

final readonly class UpdateFamilyCommand
{
    public function __construct(
        public string $id,
        public ?string $name,
        public ?bool $status,
    ) {}
}