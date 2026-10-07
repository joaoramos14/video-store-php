<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'movie_id', 'rented_at', 'due_at', 'returned_at', 'days', 'total_price'];

    protected $casts = ['rented_at' => 'datetime', 'due_at' => 'datetime', 'returned_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }
}
