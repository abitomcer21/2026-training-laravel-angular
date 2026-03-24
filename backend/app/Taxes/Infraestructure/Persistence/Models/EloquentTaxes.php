<?php

namespace App\Taxes\Infraestructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentTaxes extends Model
{
    use SoftDeletes;

    protected $table = 'taxes';

    protected $fillable = [
        'uuid',
        'name',
        'percentage',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }
}
