<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = ['tmdb_id', 'title', 'synopsis', 'year', 'duration', 'cover', 'stock', 'daily_price', 'genre_id'];

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function isAvailable() : bool
    {
        return $this->stock > 0;
    }

}
