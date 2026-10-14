<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rental extends Model
{
    use HasFactory;

    // Campos alinhados com a migration create_rentals_table (+ late_fee)
    protected $fillable = [
        'user_id',
        'movie_id',
        'rented_at',
        'due_at',
        'returned_at',
        'days',
        'total_price',
        'late_fee',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id'     => 'integer',
            'movie_id'    => 'integer',
            'days'        => 'integer',
            'total_price' => 'decimal:2',
            'late_fee'    => 'decimal:2',
            'rented_at'   => 'datetime',
            'due_at'      => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /** Aluguel não devolvido e com o prazo vencido. */
    public function isLate(): bool
    {
        return $this->status === 'late'
            || ($this->status === 'active' && $this->due_at->isPast());
    }

    /** Estado para exibir nas telas: active, late ou returned. */
    public function displayStatus(): string
    {
        if ($this->status === 'returned') {
            return 'returned';
        }

        return $this->isLate() ? 'late' : 'active';
    }
}
