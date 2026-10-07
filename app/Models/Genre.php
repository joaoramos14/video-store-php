<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['tmdb_id', 'name', 'description'];

    public function movies()
    {
        return $this->hasMany(Movie::class);
    }
}
