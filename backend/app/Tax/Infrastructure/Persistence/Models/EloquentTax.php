<?php

namespace App\Tax\Infrastructure\Persistence\Models;

use Database\Factories\TaxFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentTax extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'taxes';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'name',
        'percentage',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }

    protected static function newFactory(): TaxFactory
    {
        return TaxFactory::new();
    }
}

