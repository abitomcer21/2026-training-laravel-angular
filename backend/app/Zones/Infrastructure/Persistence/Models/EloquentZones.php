<?php

namespace App\Zones\Infrastructure\Persistence\Models;

use Database\Factories\ZonesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentZones extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'zones';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'name',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }

    protected static function newFactory(): ZonesFactory
    {
        return ZonesFactory::new();
    }
}
