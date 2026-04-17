<?php

namespace App\Family\Infrastructure\Persistence\Models;

use Database\Factories\FamilyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentFamily extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'families';

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'name',
        'active',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }

    protected static function newFactory(): FamilyFactory
    {
        return FamilyFactory::new();
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
