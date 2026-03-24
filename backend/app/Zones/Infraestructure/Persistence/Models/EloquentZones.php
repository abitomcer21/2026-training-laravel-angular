<?php

namespace App\Zones\Infraestructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentZones extends Model
{
    use SoftDeletes;

    protected $table = 'zones';

    protected $fillable = [
        'uuid',
        'name',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }
}
