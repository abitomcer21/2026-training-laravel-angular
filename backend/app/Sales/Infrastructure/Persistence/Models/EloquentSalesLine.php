<?php

namespace App\Sales\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Products\Infrastructure\Persistence\Models\EloquentProducts;
use App\User\Infrastructure\Persistence\Models\EloquentUser;

class EloquentSalesLine extends Model
{
    use SoftDeletes;

    protected $table = 'sales_lines';

    protected $fillable = [
        'uuid',
        'sale_id',
        'product_id',
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

    public function product()
    {
        return $this->belongsTo(EloquentProducts::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }
}
