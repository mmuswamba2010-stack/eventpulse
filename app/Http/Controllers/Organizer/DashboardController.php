<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tableau de bord de l'organisateur : statistiques de vente et chiffre d'affaires.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $eventIds = $user->events()->pluck('id');

        $totalEvents = $eventIds->count();

        $ticketsQuery = Ticket::whereIn('event_id', $eventIds)->where('status', '!=', 'cancelled');

        $totalTicketsSold = (clone $ticketsQuery)->count();
        $totalTicketsUsed = (clone $ticketsQuery)->where('status', 'used')->count();

        $totalRevenue = (float) Ticket::query()
            ->whereIn('tickets.event_id', $eventIds)
            ->where('tickets.status', '!=', 'cancelled')
            ->leftJoin('ticket_types', 'ticket_types.id', '=', 'tickets.ticket_type_id')
            ->leftJoin('events', 'events.id', '=', 'tickets.event_id')
            ->selectRaw('COALESCE(SUM(COALESCE(ticket_types.price, events.price)), 0) as revenue')
            ->value('revenue');

        $events = $user->events()
            ->withCount([
                'tickets as sold_count' => fn ($q) => $q->where('status', '!=', 'cancelled'),
                'tickets as used_count' => fn ($q) => $q->where('status', 'used'),
            ])
            ->orderByDesc('event_date')
            ->take(5)
            ->get()
            ->each(function ($event) {
                $event->setAttribute(
                    'revenue',
                    (float) Ticket::query()
                        ->where('tickets.event_id', $event->id)
                        ->where('tickets.status', '!=', 'cancelled')
                        ->leftJoin('ticket_types', 'ticket_types.id', '=', 'tickets.ticket_type_id')
                        ->leftJoin('events', 'events.id', '=', 'tickets.event_id')
                        ->selectRaw('COALESCE(SUM(COALESCE(ticket_types.price, events.price)), 0) as revenue')
                        ->value('revenue')
                );
            });

        $recentScans = Scan::with(['ticket.event', 'ticket.user'])
            ->whereHas('ticket', fn ($q) => $q->whereIn('event_id', $eventIds))
            ->latest()
            ->take(8)
            ->get();

        return view('organizer.dashboard', compact(
            'totalEvents',
            'totalTicketsSold',
            'totalTicketsUsed',
            'totalRevenue',
            'events',
            'recentScans',
        ));
    }
}
