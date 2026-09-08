<?php

namespace App\Support;

use App\Models\User;

class OrganizerTerms
{
    public static function version(): string
    {
        return (string) config('eventpulse.organizer_terms_version', '1.0');
    }

    public static function hasAccepted(User $user): bool
    {
        if (! $user->isOrganizer()) {
            return true;
        }

        return $user->organizer_terms_accepted_at !== null
            && $user->organizer_terms_version === self::version();
    }

    public static function recordAcceptance(User $user): void
    {
        $user->forceFill([
            'organizer_terms_accepted_at' => now(),
            'organizer_terms_version' => self::version(),
        ])->save();
    }
}
