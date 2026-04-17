<?php

namespace App\Sales\Infrastructure\Persistence\Models;

use Database\Factories\SalesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentSales extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'order_id',
        'user_id',
        'ticket_number',
        'value_date',
        'total',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }

    protected static function newFactory(): SalesFactory
    {
        return SalesFactory::new();
    }

    protected function casts(): array
    {
        return [
            'value_date' => 'datetime',
        ];
    }
}
