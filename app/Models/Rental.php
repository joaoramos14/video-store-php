<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rental extends Model
{
    use HasFactory;

    // Fluxo: reserved -> active -> returned. Desvios: cancelled (cliente desistiu) e expired (ninguém retirou).
    // O atraso não é gravado: um aluguel active com prazo vencido é calculado por isLate()/displayStatus().
    public const RESERVED  = 'reserved';
    public const ACTIVE    = 'active';
    public const LATE      = 'late';      // só para exibição (displayStatus()); nunca é gravado no banco
    public const RETURNED  = 'returned';
    public const CANCELLED = 'cancelled';
    public const EXPIRED   = 'expired';

    /** Estados que displayStatus() pode devolver (e que whereDisplayStatus() aceita). */
    public const DISPLAY_STATES = [self::RESERVED, self::ACTIVE, self::LATE, self::RETURNED, self::CANCELLED, self::EXPIRED];

    protected $fillable = [
        'user_id',
        'movie_id',
        'reserved_until',
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
            'user_id'        => 'integer',
            'movie_id'       => 'integer',
            'days'           => 'integer',
            'total_price'    => 'decimal:2',
            'late_fee'       => 'decimal:2',
            'reserved_until' => 'datetime',
            'rented_at'      => 'datetime',
            'due_at'         => 'datetime',
            'returned_at'    => 'datetime',
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

    /** Reservas ainda dentro do prazo de retirada. Mesma regra de isPickupOpen(). */
    public function scopePickupOpen(Builder $query): Builder
    {
        return $query->where('status', self::RESERVED)->where('reserved_until', '>', now());
    }

    /** Reservas cujo prazo de retirada já passou, mas que ainda não foram marcadas como expiradas. */
    public function scopeLapsedReservations(Builder $query): Builder
    {
        return $query->where('status', self::RESERVED)->where('reserved_until', '<=', now());
    }

    /** Aluguéis que impedem uma nova reserva do mesmo filme: reserva dentro do prazo ou filme com o cliente. */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->where(fn(Builder $q) => $q->pickupOpen()->orWhere('status', self::ACTIVE));
    }

    /** Versão SQL de displayStatus(): ao mudar uma, mude a outra. */
    public function scopeWhereDisplayStatus(Builder $query, string $state): Builder
    {
        return match ($state) {
            self::RESERVED => $query->pickupOpen(),
            self::ACTIVE   => $query->where('status', self::ACTIVE)->where('due_at', '>=', now()),
            self::LATE     => $query->where('status', self::ACTIVE)->where('due_at', '<', now()),
            self::EXPIRED  => $query->where(fn(Builder $q) => $q->where('status', self::EXPIRED)
                ->orWhere(fn(Builder $r) => $r->lapsedReservations())),
            default        => $query->where('status', $state),
        };
    }

    public function isReserved(): bool
    {
        return $this->status === self::RESERVED;
    }

    /** Reserva ainda dentro do prazo de retirada. */
    public function isPickupOpen(): bool
    {
        return $this->isReserved() && $this->reserved_until?->isFuture();
    }

    /** O filme está com o cliente (retirado e ainda não devolvido). */
    public function isOut(): bool
    {
        return $this->status === self::ACTIVE;
    }

    /** Filme retirado e com o prazo de devolução vencido. */
    public function isLate(): bool
    {
        return $this->isOut() && $this->due_at?->isPast();
    }

    /** O cliente chegou a retirar o filme (só nesses casos faz sentido avaliar). */
    public function wasPickedUp(): bool
    {
        return in_array($this->status, [self::ACTIVE, self::RETURNED], true);
    }

    /**
     * Estado para exibir nas telas: reserved, active, late, returned, cancelled ou expired.
     * Uma reserva com prazo vencido aparece como expirada mesmo antes da tarefa agendada rodar.
     * A versão SQL desta regra é scopeWhereDisplayStatus(): ao mudar uma, mude a outra.
     */
    public function displayStatus(): string
    {
        return match (true) {
            $this->status === self::RESERVED => $this->isPickupOpen() ? self::RESERVED : self::EXPIRED,
            $this->isOut()                   => $this->isLate() ? self::LATE : self::ACTIVE,
            default                          => $this->status,
        };
    }
}
