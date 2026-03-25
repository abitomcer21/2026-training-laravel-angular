<?php

namespace App\Restaurants\Infraestructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentRestaurants extends Model
{
    use SoftDeletes;

    protected $table = 'restaurants';

    protected $fillable = [
        'uuid',
        'name',
        'legal_name',
        'tax_id',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }
}