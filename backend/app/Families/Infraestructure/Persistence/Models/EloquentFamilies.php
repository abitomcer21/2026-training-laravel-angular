<?php

namespace App\Families\Infraestructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentFamilies extends Model
{
    use SoftDeletes;

    protected $table = 'families';

    protected $fillable = [
        'uuid',
        'name',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function getKeyName(): string
    {   
        return 'id';
    }
}
