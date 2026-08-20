<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->role === 'organizer';
    }

    public function moderate(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->role === 'organizer';
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }
}
