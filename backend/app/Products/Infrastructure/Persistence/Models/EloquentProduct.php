<?php

namespace App\Products\Infrastructure\Persistence\Models;

use Database\Factories\ProductsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'family_id',
        'tax_id',
        'image_src',
        'name',
        'price',
        'stock',
        'active',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }

    protected static function newFactory(): ProductsFactory
    {
        return ProductsFactory::new();
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
