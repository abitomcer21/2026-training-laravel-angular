<?php

namespace App\User\Domain\Entity;

use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Email;
use App\Shared\Domain\ValueObject\Uuid;
use App\User\Domain\ValueObject\Pin;
use App\User\Domain\ValueObject\PasswordHash;
use App\User\Domain\ValueObject\UserName;
use App\User\Domain\ValueObject\Role;

class User
{
    private function __construct(
        private Uuid $id,
        private UserName $name,
        private Email $email,
        private PasswordHash $passwordHash,
        private Role $role,
        private ?string $imageSrc,
        private int $restaurantId,
        private Pin $pin,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(
        Email $email,
        UserName $name,
        PasswordHash $passwordHash,
        Role $role,
        Pin $pin,
        int $restaurantId,
        ?string $imageSrc = null,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $email,
            $passwordHash,
            $role,
            $imageSrc,
            $restaurantId,
            $pin,
            $now,
            $now,
        );
    }

public static function fromPersistence(
    string $id,
    string $name,
    string $email,
    string $passwordHash,
    string $role,
    int $restaurantId,
    string $pin,
    ?string $imageSrc,
    \DateTimeImmutable $createdAt,
    \DateTimeImmutable $updatedAt,
): self {
    return new self(
        Uuid::create($id),
        UserName::create($name),
        Email::create($email),
        PasswordHash::create($passwordHash),
        Role::create($role),
        $imageSrc,
        $restaurantId,
        Pin::create($pin),
        DomainDateTime::create($createdAt),
        DomainDateTime::create($updatedAt),
    );
}

    public function updateData(
        Email $email,
        UserName $name,
        PasswordHash $passwordHash,
        Role $role,
        ?string $imageSrc,
        Pin $pin
    ): self {
        return new self(
            $this->id,
            $name,
            $email,
            $passwordHash,
            $role,
            $imageSrc,
            $this->restaurantId,
            $pin,
            $this->createdAt,
            DomainDateTime::now(),
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): UserName
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function passwordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function imageSrc(): ?string
    {
        return $this->imageSrc;
    }

    public function restaurantId(): int
    {
        return $this->restaurantId;
    }

    public function pin(): Pin
    {
        return $this->pin;
    }

    public function createdAt(): DomainDateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DomainDateTime
    {
        return $this->updatedAt;
    }
}
