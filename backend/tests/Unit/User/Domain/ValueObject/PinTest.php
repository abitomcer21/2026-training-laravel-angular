<?php

namespace Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\Pin;
use PHPUnit\Framework\TestCase;

class PinTest extends TestCase
{
    public function test_valid_pin(): void
    {
        $pin = Pin::create('1234');
        $this->assertSame('1234', $pin->value());
        $this->assertTrue($pin->hasPin());
    }

    public function test_null_pin(): void
    {
        $pin = Pin::create(null);
        $this->assertNull($pin->value());
        $this->assertFalse($pin->hasPin());
    }

    public function test_invalid_pin_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Pin::create('123');
    }

    public function test_pin_with_letters_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Pin::create('12ab');
    }
}