<?php

namespace App\Tables\Infrastructure\Persistence\Models;

use Database\Factories\TablesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentTables extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tables';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'zone_id',
        'name',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }

    protected static function newFactory(): TablesFactory
    {
        return TablesFactory::new();
    }
}
