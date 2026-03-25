<?php

namespace App\Families\Infraestructure\Persistence\Models;

use Database\Factories\FamilyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentFamilies extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'families';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'name',
        'activo',
    ];

    protected static function newFactory(): FamilyFactory
    {
        return FamilyFactory::new();
    }

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function getKeyName(): string
    {
        return 'id';
    }
}

