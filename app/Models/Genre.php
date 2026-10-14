<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'tmdb_id' => 'integer',
        ];
    }

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }
}
