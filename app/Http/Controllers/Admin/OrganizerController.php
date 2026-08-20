<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerController extends Controller
{
    public function index(): View
    {
        $organizers = User::query()
            ->where('role', 'organizer')
            ->withCount([
                'events',
                'events as published_events_count' => fn ($q) => $q->where('status', 'published'),
                'events as cancelled_events_count' => fn ($q) => $q->where('status', 'cancelled'),
            ])
            ->addSelect([
                'active_tickets_count' => Ticket::query()
                    ->selectRaw('count(*)')
                    ->join('events', 'events.id', '=', 'tickets.event_id')
                    ->whereColumn('events.user_id', 'users.id')
                    ->where('tickets.status', '!=', 'cancelled'),
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.organizers.index', compact('organizers'));
    }

    public function approve(Request $request, User $organizer): RedirectResponse
    {
        $this->authorize('moderate', $organizer);
        abort_unless($organizer->role === 'organizer', 404);

        $organizer->update([
            'organizer_status' => 'approved',
            'organizer_reviewed_at' => now(),
            'organizer_moderation_note' => $request->input('note'),
        ]);

        return redirect()
            ->route('admin.organizers.index')
            ->with('admin_success', __('Organizer approved', ['name' => $organizer->name]));
    }

    public function reject(Request $request, User $organizer): RedirectResponse
    {
        $this->authorize('moderate', $organizer);
        abort_unless($organizer->role === 'organizer', 404);

        $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $organizer->update([
            'organizer_status' => 'rejected',
            'organizer_reviewed_at' => now(),
            'organizer_moderation_note' => $request->input('note'),
        ]);

        return redirect()
            ->route('admin.organizers.index')
            ->with('admin_success', __('Organizer rejected', ['name' => $organizer->name]));
    }

    public function suspend(User $organizer): RedirectResponse
    {
        $this->authorize('moderate', $organizer);
        abort_unless($organizer->role === 'organizer', 404);

        $organizer->update(['suspended_at' => now()]);

        return redirect()
            ->route('admin.organizers.index')
            ->with('admin_success', __('Organizer suspended', ['name' => $organizer->name]));
    }

    public function unsuspend(User $organizer): RedirectResponse
    {
        $this->authorize('moderate', $organizer);
        abort_unless($organizer->role === 'organizer', 404);

        $organizer->update(['suspended_at' => null]);

        return redirect()
            ->route('admin.organizers.index')
            ->with('admin_success', __('Organizer unsuspended', ['name' => $organizer->name]));
    }

    public function destroy(Request $request, User $organizer): RedirectResponse
    {
        $this->authorize('delete', $organizer);
        abort_unless($organizer->role === 'organizer', 404);

        $request->validate([
            'confirm' => ['required', 'in:DELETE'],
        ]);

        $activeTickets = Ticket::query()
            ->whereHas('event', fn ($q) => $q->where('user_id', $organizer->id))
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($activeTickets > 0) {
            return redirect()
                ->route('admin.organizers.index')
                ->with('admin_error', __('Organizer delete blocked tickets', [
                    'count' => $activeTickets,
                    'name' => $organizer->name,
                ]));
        }

        $name = $organizer->name;
        $eventsCount = $organizer->events()->count();

        $organizer->events()->each(fn (Event $event) => $event->delete());
        $organizer->delete();

        return redirect()
            ->route('admin.organizers.index')
            ->with('admin_success', __('Organizer deleted', [
                'name' => $name,
                'events' => $eventsCount,
            ]));
    }
}
