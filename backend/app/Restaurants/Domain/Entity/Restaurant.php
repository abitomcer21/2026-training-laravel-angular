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
        private ?string $imageSrc,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(
        RestaurantName $name,
        RestaurantLegalName $legalName,
        RestaurantTaxId $taxId,
        Email $email,
        RestaurantPassword $password,
        ?string $imageSrc = null,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $legalName,
            $taxId,
            $email,
            $password,
            $imageSrc,
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
        ?string $imageSrc,
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
            $imageSrc,
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
        );
    }

    public function updateData(
        RestaurantName $name,
        RestaurantLegalName $legalName,
        RestaurantTaxId $taxId,
        Email $email,
        ?string $imageSrc,
    ): self {
        return new self(
            $this->id,
            $name,
            $legalName,
            $taxId,
            $email,
            $this->password,
            $imageSrc,
            $this->createdAt,
            DomainDateTime::now(),
        );
    }

    // Getters
    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): RestaurantName
    {
        return $this->name;
    }

    public function legalName(): RestaurantLegalName
    {
        return $this->legalName;
    }

    public function taxId(): RestaurantTaxId
    {
        return $this->taxId;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): RestaurantPassword
    {
        return $this->password;
    }

    public function imageSrc(): ?string
    {
        return $this->imageSrc;
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