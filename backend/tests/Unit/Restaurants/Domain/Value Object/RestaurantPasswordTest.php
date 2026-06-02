<?php

namespace Tests\Unit\Restaurants\Domain\ValueObject;

use App\Restaurants\Domain\ValueObject\RestaurantPassword;
use PHPUnit\Framework\TestCase;

class RestaurantPasswordTest extends TestCase
{
    public function test_crea_contrasena_valida(): void
    {
        $password = RestaurantPassword::create('Secret123!');
        $this->assertSame('Secret123!', $password->value());
    }

    public function test_lanza_excepcion_si_es_demasiado_corta(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant password must be at least 8 characters.');
        RestaurantPassword::create('Sec1!');
    }

    public function test_lanza_excepcion_si_es_demasiado_larga(): void
    {
        $demasiadoLarga = str_repeat('a', 256) . 'A1!';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant password cannot exceed 255 characters.');
        RestaurantPassword::create($demasiadoLarga);
    }

    public function test_lanza_excepcion_si_esta_vacia(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Restaurant password cannot be empty.');
        RestaurantPassword::create('');
    }

    public function test_lanza_excepcion_si_falta_mayuscula(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain at least one uppercase letter');
        RestaurantPassword::create('secret123!');
    }

    public function test_lanza_excepcion_si_falta_minuscula(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain at least one lowercase letter');
        RestaurantPassword::create('SECRET123!');
    }

    public function test_lanza_excepcion_si_falta_numero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain at least one number');
        RestaurantPassword::create('SecretPassword!');
    }

    public function test_lanza_excepcion_si_falta_caracter_especial(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain at least one special character');
        RestaurantPassword::create('Secret123');
    }
}