<?php

namespace App\Restaurants\Application\Command;

use App\Restaurants\Domain\ValueObject\RestaurantLegalName;
use App\Restaurants\Domain\ValueObject\RestaurantName;
use App\Restaurants\Domain\ValueObject\RestaurantTaxId;
use App\Shared\Domain\ValueObject\Email;

final readonly class CreateRestaurantCommand
{
    private function __construct(
        public RestaurantName $name,
        public RestaurantLegalName $legalName,
        public RestaurantTaxId $taxId,
        public Email $email,
        public string $plainPassword,
    ) {}

    public static function create(
        string $name,
        string $legalName,
        string $taxId,
        string $email,
        string $plainPassword,
    ): self {
        return new self(
            name:          RestaurantName::create($name),
            legalName:     RestaurantLegalName::create($legalName),
            taxId:         RestaurantTaxId::create($taxId),
            email:         Email::create($email),
            plainPassword: $plainPassword,
        );
    }
}
