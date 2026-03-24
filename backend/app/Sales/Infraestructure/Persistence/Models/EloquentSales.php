<?php

namespace App\Sales\Infraestructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentSales extends Model
{
    use SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'uuid',
        'table_id',
        'opened_by_user_id',
        'closed_by_user_id',
        'status',
        'diners',
        'opened_at',
        'closed_at',
        'ticket_number',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function getKeyName(): string
    {
        return 'id';
    }
}
