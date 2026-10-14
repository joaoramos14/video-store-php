<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory;

    // Campos alinhados com a migration create_movies_table
    protected $fillable = [
        'tmdb_id',
        'genre_id',
        'title',
        'director',
        'synopsis',
        'year',
        'duration',
        'cover',
        'stock',
        'daily_price',
    ];

    protected function casts(): array
    {
        return [
            'tmdb_id'     => 'integer',
            'genre_id'    => 'integer',
            'year'        => 'integer',
            'duration'    => 'integer',
            'stock'       => 'integer',
            'daily_price' => 'decimal:2',
        ];
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
