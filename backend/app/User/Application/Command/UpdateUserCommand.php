<?php

namespace App\User\Application\Command;

use App\Shared\Domain\ValueObject\Email;
use App\Shared\Domain\ValueObject\Uuid;
use App\User\Domain\ValueObject\Pin;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserName;

final readonly class UpdateUserCommand
{
    private function __construct(
        public Uuid $id,
        public ?Email $email,
        public ?UserName $name,
        public ?string $plainPassword,
        public ?Role $role,
        public ?string $imageSrc,
        public ?Pin $pin,
    ) {}

    public static function create(
        string $id,
        ?string $email,
        ?string $name,
        ?string $plainPassword,
        ?string $role,
        ?string $imageSrc,
        ?string $pin,
    ): self {
        return new self(
            id:            Uuid::create($id),
            email:         $email !== null ? Email::create($email) : null,
            name:          $name !== null ? UserName::create($name) : null,
            plainPassword: $plainPassword,
            role:          $role !== null ? Role::create($role) : null,
            imageSrc:      $imageSrc,
            pin:           $pin !== null ? Pin::create($pin) : null,
        );
    }
}
