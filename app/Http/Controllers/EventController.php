<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\Seo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $this->resolveCategory($request);
        $when = $this->resolveWhen($request);
        $showFeatured = $search === '' && $category === null && $when === null;
        $featuredEvents = $showFeatured ? $this->featuredEvents() : collect();
        $excludeIds = $featuredEvents->pluck('id')->all();
        $events = $this->publishedEvents($request, $excludeIds);
        $categoryCounts = $this->categoryCounts();
        $seo = Seo::forHome();

        return view('events.index', compact('events', 'search', 'category', 'when', 'featuredEvents', 'showFeatured', 'categoryCounts', 'seo'));
    }

    public function grid(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $this->resolveCategory($request);
        $when = $this->resolveWhen($request);
        $showFeatured = $search === '' && $category === null && $when === null;
        $excludeIds = $showFeatured ? $this->featuredEvents()->pluck('id')->all() : [];
        $events = $this->publishedEvents($request, $excludeIds);

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

        $seo = Seo::forEvent($event);

        return view('events.show', compact('event', 'alreadyBooked', 'seo'));
    }

    /**
     * @return LengthAwarePaginator<int, Event>
     */
    /**
     * @param  list<int>  $excludeIds
     * @return LengthAwarePaginator<int, Event>
     */
    private function publishedEvents(Request $request, array $excludeIds = []): LengthAwarePaginator
    {
        $query = Event::query()
            ->visibleInCatalog()
            ->where('event_date', '>=', now())
            ->with('ticketTypes')
            ->withCount(['tickets' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->orderBy('event_date');

        if ($excludeIds !== []) {
            $query->whereNotIn('id', $excludeIds);
        }

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

    /**
     * @return Collection<int, Event>
     */
    private function featuredEvents(): Collection
    {
        $limit = max(1, (int) config('eventpulse.catalog.featured_count', 4));

        $base = Event::query()
            ->visibleInCatalog()
            ->where('event_date', '>=', now())
            ->with('ticketTypes')
            ->withCount(['tickets' => fn ($q) => $q->where('status', '!=', 'cancelled')]);

        $popular = (clone $base)
            ->orderByDesc('tickets_count')
            ->orderBy('event_date')
            ->limit($limit)
            ->get()
            ->filter(fn (Event $event) => ! $event->isSoldOut())
            ->values();

        if ($popular->count() >= $limit) {
            return $popular->take($limit);
        }

        $fill = (clone $base)
            ->when($popular->isNotEmpty(), fn (Builder $query) => $query->whereNotIn('id', $popular->pluck('id')))
            ->orderBy('event_date')
            ->limit($limit - $popular->count())
            ->get()
            ->filter(fn (Event $event) => ! $event->isSoldOut())
            ->values();

        return $popular->concat($fill)->take($limit);
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
