<?php

namespace App\Support;

use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminInsights
{
    public static function stats(): array
    {
        return [
            'organizers' => User::where('role', 'organizer')->count(),
            'participants' => User::where('role', 'participant')->count(),
            'events_published' => Event::where('status', 'published')->count(),
            'events_draft' => Event::where('status', 'draft')->count(),
            'events_cancelled' => Event::where('status', 'cancelled')->count(),
            'tickets_sold' => Ticket::where('status', '!=', 'cancelled')->count(),
            'newsletter_active' => NewsletterSubscriber::active()->count(),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public static function suspiciousParticipants(int $ticketThreshold = 5, int $days = 7): Collection
    {
        $since = now()->subDays($days);

        return User::query()
            ->where('role', 'participant')
            ->where(function ($query) use ($since, $ticketThreshold) {
                $query->whereHas('tickets', function ($q) use ($since) {
                    $q->where('created_at', '>=', $since)->where('status', '!=', 'cancelled');
                }, '>=', $ticketThreshold)
                    ->orWhereHas('tickets', function ($q) {
                        $q->where('status', 'cancelled');
                    }, '>=', 3);
            })
            ->withCount([
                'tickets as recent_tickets_count' => fn ($q) => $q->where('created_at', '>=', $since)
                    ->where('status', '!=', 'cancelled'),
                'tickets as cancelled_tickets_count' => fn ($q) => $q->where('status', 'cancelled'),
            ])
            ->orderByDesc('recent_tickets_count')
            ->limit(20)
            ->get()
            ->map(function (User $user) use ($ticketThreshold) {
                $reasons = [];
                if ($user->recent_tickets_count >= $ticketThreshold) {
                    $reasons[] = "{$user->recent_tickets_count} billets récents";
                }
                if ($user->cancelled_tickets_count >= 3) {
                    $reasons[] = "{$user->cancelled_tickets_count} billets annulés";
                }
                $user->setAttribute('alert_reasons', $reasons);

                return $user;
            });
    }

    /**
     * @return Collection<int, User>
     */
    public static function flaggedOrganizers(): Collection
    {
        return User::query()
            ->where('role', 'organizer')
            ->where(function ($query) {
                $query->whereHas('events', fn ($q) => $q->where('status', 'cancelled'))
                    ->orWhereHas('events', fn ($q) => $q->where('status', 'draft'), '>=', 3);
            })
            ->withCount([
                'events as published_events_count' => fn ($q) => $q->where('status', 'published'),
                'events as cancelled_events_count' => fn ($q) => $q->where('status', 'cancelled'),
                'events as draft_events_count' => fn ($q) => $q->where('status', 'draft'),
            ])
            ->orderByDesc('cancelled_events_count')
            ->limit(20)
            ->get()
            ->map(function (User $user) {
                $reasons = [];
                if ($user->cancelled_events_count > 0) {
                    $reasons[] = "{$user->cancelled_events_count} événement(s) annulé(s)";
                }
                if ($user->draft_events_count >= 3) {
                    $reasons[] = "{$user->draft_events_count} brouillons";
                }
                $user->setAttribute('alert_reasons', $reasons);

                return $user;
            });
    }

    /**
     * @return Collection<int, Event>
     */
    public static function recentPublishedEvents(int $days = 7): Collection
    {
        return Event::query()
            ->with('user:id,name,email')
            ->where('status', 'published')
            ->where('updated_at', '>=', now()->subDays($days))
            ->orderByDesc('event_date')
            ->get();
    }
}
