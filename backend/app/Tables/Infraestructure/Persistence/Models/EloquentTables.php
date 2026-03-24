<?php

namespace App\Tables\Infraestructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentTables extends Model
{
    use SoftDeletes;

    protected $table = 'tables';

    protected $fillable = [
        'uuid',
        'zone_id',
        'name',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }
}
