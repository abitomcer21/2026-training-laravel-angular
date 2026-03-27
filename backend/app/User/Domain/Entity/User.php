<?php

namespace App\User\Domain\Entity;

use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Email;
use App\Shared\Domain\ValueObject\Uuid;
use App\User\Domain\ValueObject\PasswordHash;
use App\User\Domain\ValueObject\UserName;
USE App\User\Domain\ValueObject\Pin;

class User
{
    private function __construct(
        private Uuid $id,
        private UserName $name,
        private Email $email,
        private PasswordHash $passwordHash,
        private string $role,
        private ?string $imageSrc,
        private ?int $restaurantId,
        private Pin $pin,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {
    }

    public static function dddCreate(
        Email $email,
        UserName $name,
        PasswordHash $passwordHash,
        string $role,
        Pin $pin,
        ?string $imageSrc = null,
        ?int $restaurantId = null,
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
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        string $role = 'admin',
        ?string $imageSrc = null,
        ?int $restaurantId = null,
        string $pin = '0000',
    ): self {
        return new self(
            Uuid::create($id),
            UserName::create($name),
            Email::create($email),
            PasswordHash::create($passwordHash),
            $role,
            $imageSrc,
            $restaurantId,
            Pin::create($pin),
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name->value();
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash->value();
    }

    public function role(): string
    {
        return $this->role;
    }

    public function imageSrc(): ?string
    {
        return $this->imageSrc;
    }

    public function restaurantId(): ?int
    {
        return $this->restaurantId;
    }

    public function pin(): string
    {
        return $this->pin->value();
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
