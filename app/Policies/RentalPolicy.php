<?php

namespace App\Policies;

use App\Models\Rental;
use App\Models\User;

class RentalPolicy
{
    /** Só o próprio cliente cancela ou avalia o aluguel. Usada nas rotas com ->can('own', 'rental'). */
    public function own(User $user, Rental $rental): bool
    {
        return $rental->user_id === $user->id;
    }
}
