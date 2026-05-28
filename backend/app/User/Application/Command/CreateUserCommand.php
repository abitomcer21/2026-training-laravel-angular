<?php

namespace App\User\Application\Command;

use App\Shared\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Pin;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserName;

final readonly class CreateUserCommand
{
    private function __construct(
        public Email $email,
        public UserName $name,
        public string $plainPassword,
        public Role $role,
        public Pin $pin,
        public int $restaurantId,
        public ?string $imageSrc,
    ) {}

    public static function create(
        string $email,
        string $name,
        string $plainPassword,
        string $role,
        string $pin,
        int $restaurantId,
        ?string $imageSrc = null,
    ): self {
        return new self(
            email:        Email::create($email),
            name:         UserName::create($name),
            plainPassword: $plainPassword,
            role:         Role::create($role),
            pin:          Pin::create($pin),
            restaurantId: $restaurantId,
            imageSrc:     $imageSrc,
        );
    }
}
