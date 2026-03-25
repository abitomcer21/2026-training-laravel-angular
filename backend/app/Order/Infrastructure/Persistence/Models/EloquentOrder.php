<?php

namespace App\Order\Infrastructure\Persistence\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'status',
        'table_id',
        'opened_by_user_id',
        'closed_by_user_id',
        'diners',
        'opened_at',
        'closed_at',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
