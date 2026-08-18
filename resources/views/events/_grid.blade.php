@if ($events->isEmpty())
    <div class="ep-card px-6 py-16 text-center" data-total="0">
        <img src="{{ asset('images/brand/mark.svg') }}" alt="" class="h-12 w-12 mx-auto mb-4 opacity-30">
        @if (! empty($search) || ! empty($category))
            <p class="font-display text-lg font-semibold text-charcoal">Aucun événement trouvé</p>
            <p class="text-sm text-frost mt-2">
                <a href="{{ route('events.index') }}" class="ep-btn-outline text-sm mt-4 no-underline">Voir tout le catalogue</a>
            </p>
        @else
            <p class="font-display text-lg font-semibold text-charcoal">Rien pour l'instant</p>
            <p class="text-sm text-frost mt-2">De nouveaux événements arrivent bientôt.</p>
        @endif
    </div>
@else
    <div data-total="{{ $events->total() }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
            @foreach ($events as $event)
                <x-event-card :event="$event" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $events->links() }}
        </div>
    </div>
@endif
