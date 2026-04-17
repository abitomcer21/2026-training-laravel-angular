<?php

namespace App\Sales\Infrastructure\Persistence\Models;

use App\Order\Infrastructure\Persistence\Models\EloquentOrderLine;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentSalesLine extends Model
{
    use SoftDeletes;

    protected $table = 'sales_lines';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'sale_id',
        'order_line_id',
        'user_id',
        'quantity',
        'price',
        'tax_percentage',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
        'tax_percentage' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(EloquentSales::class, 'sale_id');
    }

    public function orderLine()
    {
        return $this->belongsTo(EloquentOrderLine::class, 'order_line_id');
    }

    public function user()
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }
}
