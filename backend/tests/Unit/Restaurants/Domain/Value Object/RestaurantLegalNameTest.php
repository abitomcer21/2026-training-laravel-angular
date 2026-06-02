<?php

namespace Tests\Unit\Restaurants\Domain\ValueObject;

use App\Restaurants\Domain\ValueObject\RestaurantLegalName;
use PHPUnit\Framework\TestCase;

class RestaurantLegalNameTest extends TestCase
{
    public function test_it_creates_a_valid_legal_name(): void
    {
        $legalName = RestaurantLegalName::create('Mi Restaurante S.L.');
        $this->assertSame('Mi Restaurante S.L.', $legalName->value());
    }

    public function test_it_trims_whitespace(): void
    {
        $legalName = RestaurantLegalName::create('  Mi Restaurante S.L.  ');
        $this->assertSame('Mi Restaurante S.L.', $legalName->value());
    }

    public function test_it_throws_exception_when_legal_name_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant legal name cannot be empty.');
        RestaurantLegalName::create('');
    }

    public function test_it_throws_exception_when_legal_name_consists_only_of_spaces(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant legal name cannot be empty.');
        RestaurantLegalName::create('   ');
    }

    public function test_it_throws_exception_when_legal_name_exceeds_max_length(): void
    {
        $tooLong = str_repeat('A', 256);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant legal name cannot exceed 255 characters.');
        RestaurantLegalName::create($tooLong);
    }
}