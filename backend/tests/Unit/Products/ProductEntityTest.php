<?php

namespace Tests\Unit\Products;

use App\Products\Domain\Entity\Product;
use App\Products\Domain\ValueObject\ProductImageSrc;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductPrice;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Products\Domain\ValueObject\ProductStock;
use App\Shared\Domain\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

class ProductEntityTest extends TestCase
{
    public function test_activate_marks_product_as_active(): void
    {
        $product = Product::dddCreate(
            Uuid::generate(),
            Uuid::generate(),
            ProductName::create('Café'),
            ProductPrice::create(150),
            ProductStock::create(10),
            ProductImageSrc::create('/images/cafe.png'),
            ProductStatus::inactive(),
            1,
        );

        $product->activate();

        $this->assertTrue($product->status()->isActive());
    }

    public function test_deactivate_marks_product_as_inactive(): void
    {
        $product = Product::dddCreate(
            Uuid::generate(),
            Uuid::generate(),
            ProductName::create('Tostada'),
            ProductPrice::create(250),
            ProductStock::create(5),
            ProductImageSrc::create('/images/tostada.png'),
            ProductStatus::active(),
            1,
        );

        $product->deactivate();

        $this->assertFalse($product->status()->isActive());
    }
}
