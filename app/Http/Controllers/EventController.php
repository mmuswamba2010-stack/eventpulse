<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $this->resolveCategory($request);
        $when = $this->resolveWhen($request);
        $events = $this->publishedEvents($request);
        $featuredEvent = $this->featuredEvent();
        $categoryCounts = $this->categoryCounts();

        return view('events.index', compact('events', 'search', 'category', 'when', 'featuredEvent', 'categoryCounts'));
    }

    public function grid(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $this->resolveCategory($request);
        $when = $this->resolveWhen($request);
        $events = $this->publishedEvents($request);

        return view('events._grid', compact('events', 'search', 'category', 'when'));
    }

    public function show(string $slug): View
    {
        $event = Event::where('slug', $slug)
            ->visibleInCatalog()
            ->with(['user', 'ticketTypes'])
            ->withCount(['tickets' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->firstOrFail();

        $alreadyBooked = auth()->check()
            ? $event->tickets()->where('user_id', auth()->id())->where('status', '!=', 'cancelled')->exists()
            : false;

        return view('events.show', compact('event', 'alreadyBooked'));
    }

    /**
     * @return LengthAwarePaginator<int, Event>
     */
    private function publishedEvents(Request $request): LengthAwarePaginator
    {
        $query = Event::query()
            ->visibleInCatalog()
            ->where('event_date', '>=', now())
            ->with('ticketTypes')
            ->withCount(['tickets' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->orderBy('event_date');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($category = $this->resolveCategory($request)) {
            $query->where('category', $category);
        }

        $this->applyWhenFilter($query, $this->resolveWhen($request));

        return $query
            ->paginate(9)
            ->withQueryString()
            ->withPath(route('events.index'));
    }

    private function resolveCategory(Request $request): ?string
    {
        $category = $request->string('category')->trim()->toString();

        if ($category === '' || $category === 'all') {
            return null;
        }

        return array_key_exists($category, Event::CATEGORIES) ? $category : null;
    }

    private function resolveWhen(Request $request): ?string
    {
        $when = $request->string('when')->trim()->toString();

        return in_array($when, ['today', 'weekend'], true) ? $when : null;
    }

    /**
     * @param  Builder<Event>  $query
     */
    private function applyWhenFilter(Builder $query, ?string $when): void
    {
        if ($when === 'today') {
            $query->whereDate('event_date', now()->toDateString());

            return;
        }

        if ($when === 'weekend') {
            $now = now();
            if ($now->isSaturday()) {
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
            } elseif ($now->isSunday()) {
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
            } else {
                $start = $now->copy()->next('Saturday')->startOfDay();
                $end = $now->copy()->next('Sunday')->endOfDay();
            }

            $query->whereBetween('event_date', [$start, $end]);
        }
    }

    private function featuredEvent(): ?Event
    {
        return Event::query()
            ->visibleInCatalog()
            ->where('event_date', '>=', now())
            ->withCount(['tickets' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->orderBy('event_date')
            ->first();
    }

    /**
     * @return array<string, int>
     */
    private function categoryCounts(): array
    {
        return Event::query()
            ->visibleInCatalog()
            ->where('event_date', '>=', now())
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
