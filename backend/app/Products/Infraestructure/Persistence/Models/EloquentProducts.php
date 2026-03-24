<?php

namespace App\Products\Infraestructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentProducts extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'family_id',
        'tax_id',
        'name',
        'price',
        'stock',
        'image_src',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function getKeyName(): string
    {
        return 'id';
    }
}
