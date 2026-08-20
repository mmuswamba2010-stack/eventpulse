<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }

    public function purchase(User $user): bool
    {
        return $user->isParticipant() || $user->isOrganizer();
    }

    public function download(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket) && in_array($ticket->status, ['valid', 'used'], true);
    }
}
