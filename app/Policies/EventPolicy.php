<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isOrganizer() && $user->canAccessOrganizerSpace();
    }

    public function view(User $user, Event $event): bool
    {
        return $user->isOrganizer()
            && $user->canAccessOrganizerSpace()
            && $event->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isOrganizer() && $user->canAccessOrganizerSpace();
    }

    public function update(User $user, Event $event): bool
    {
        return $this->view($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->view($user, $event);
    }

    public function pay(User $user, Event $event): bool
    {
        return $this->view($user, $event);
    }
}
