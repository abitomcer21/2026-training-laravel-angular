<?php

namespace App\Restaurants\Domain\Entity;

use App\Restaurants\Domain\ValueObject\RestaurantLegalName;
use App\Restaurants\Domain\ValueObject\RestaurantName;
use App\Restaurants\Domain\ValueObject\RestaurantPassword;
use App\Restaurants\Domain\ValueObject\RestaurantTaxId;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Email;
use App\Shared\Domain\ValueObject\Uuid;

class Restaurant
{
    private function __construct(
        private Uuid $id,
        private RestaurantName $name,
        private RestaurantLegalName $legalName,
        private RestaurantTaxId $taxId,
        private Email $email,
        private RestaurantPassword $password,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(
        RestaurantName $name,
        RestaurantLegalName $legalName,
        RestaurantTaxId $taxId,
        Email $email,
        RestaurantPassword $password,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $legalName,
            $taxId,
            $email,
            $password,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        string $legalName,
        string $taxId,
        string $email,
        string $password,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            Uuid::create($id),
            RestaurantName::create($name),
            RestaurantLegalName::create($legalName),
            RestaurantTaxId::create($taxId),
            Email::create($email),
            RestaurantPassword::create($password),
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

    public function legalName(): string
    {
        return $this->legalName->value();
    }

    public function taxId(): string
    {
        return $this->taxId->value();
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->password->value();
    }

    public function createdAt(): DomainDateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DomainDateTime
    {
        return $this->updatedAt;
    }

    public function updateName(RestaurantName $name): void
    {
        $this->name = $name;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updateLegalName(RestaurantLegalName $legalName): void
    {
        $this->legalName = $legalName;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updateTaxId(RestaurantTaxId $taxId): void
    {
        $this->taxId = $taxId;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updateEmail(Email $email): void
    {
        $this->email = $email;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updatePassword(RestaurantPassword $password): void
    {
        $this->password = $password;
        $this->updatedAt = DomainDateTime::now();
    }
}
