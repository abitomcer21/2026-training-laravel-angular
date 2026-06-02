<?php

namespace Tests\Unit\Restaurants\Domain\ValueObject;

use App\Restaurants\Domain\ValueObject\RestaurantTaxId;
use PHPUnit\Framework\TestCase;

class RestaurantTaxIdTest extends TestCase
{
    public function test_it_creates_a_valid_tax_id(): void
    {
        $taxId = RestaurantTaxId::create('B12345678');
        $this->assertSame('B12345678', $taxId->value());
    }

    public function test_it_trims_whitespace(): void
    {
        $taxId = RestaurantTaxId::create('  B12345678  ');
        $this->assertSame('B12345678', $taxId->value());
    }

    public function test_it_throws_exception_when_tax_id_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant tax ID cannot be empty.');
        RestaurantTaxId::create('');
    }

    public function test_it_throws_exception_when_tax_id_consists_only_of_spaces(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant tax ID cannot be empty.');
        RestaurantTaxId::create('   ');
    }

    public function test_it_throws_exception_when_tax_id_exceeds_max_length(): void
    {
        $tooLong = str_repeat('A', 256);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant tax ID cannot exceed 255 characters.');
        RestaurantTaxId::create($tooLong);
    }
}