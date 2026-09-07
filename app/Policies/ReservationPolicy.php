<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $reservation->user_id === $user->id;
    }

    public function download(User $user, Reservation $reservation): bool
    {
        return $this->view($user, $reservation);
    }
}
