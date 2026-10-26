<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'daily_price',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'daily_price' => 'decimal:2',
        ];
    }

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }
}
