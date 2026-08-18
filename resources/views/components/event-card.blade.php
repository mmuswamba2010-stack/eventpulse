@props(['event'])

@php
    $remaining = max(0, $event->capacity - ($event->tickets_count ?? 0));
    $start = $event->startingPrice();
    $sold = (int) ($event->tickets_count ?? 0);
    $participantsLabel = match (true) {
        $sold === 0 => 'Aucun inscrit',
        $sold === 1 => '1 participant',
        default => number_format($sold, 0, ',', ' ').' participants',
    };
    $dateLabel = $event->event_date->locale(app()->getLocale())->translatedFormat('D d M · H\hi');
    $cat = $event->category ?? 'other';
    $placeholderTone = match ($cat) {
        'music' => 'bg-coral-muted',
        'tech', 'conference' => 'bg-violet-muted',
        'sport' => 'bg-[#E8E8EA]',
        'art' => 'bg-violet-muted/60',
        default => 'bg-[#ECECEE]',
    };
@endphp

<a href="{{ route('events.show', $event->slug) }}"
   {{ $attributes->class(['group ep-event-card flex flex-col no-underline']) }}>
    <div class="relative h-44 bg-[#ECECEE] border-b border-charcoal/[0.06]">
        @if ($event->image_path)
            <img src="{{ asset('storage/'.$event->image_path) }}" alt="{{ $event->title }}"
                 class="absolute inset-0 w-full h-full object-cover">
        @else
            <div class="absolute inset-0 flex items-center justify-center {{ $placeholderTone }}">
                <span class="font-display text-3xl font-bold text-charcoal/15 uppercase tracking-wider">
                    {{ mb_substr(\App\Models\Event::CATEGORIES[$cat] ?? 'Ev', 0, 3) }}
                </span>
            </div>
        @endif

        <div class="absolute top-3 left-3 z-10">
            <x-category-badge :category="$cat" />
        </div>

        @if ($remaining <= 0)
            <span class="absolute top-3 right-3 z-10 px-2 py-1 rounded-md bg-charcoal text-white text-[10px] font-semibold uppercase">
                Complet
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-semibold text-[16px] leading-snug text-charcoal line-clamp-2">
            {{ $event->title }}
        </h3>

        <ul class="mt-3.5 space-y-2">
            <li class="flex items-start gap-2 text-sm text-frost">
                <x-icon name="map-pin" class="w-4 h-4 shrink-0 mt-0.5" />
                <span class="line-clamp-1">{{ $event->location }}</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-frost">
                <x-icon name="clock" class="w-4 h-4 shrink-0 mt-0.5" />
                <span>{{ $dateLabel }}</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-frost">
                <x-icon name="users" class="w-4 h-4 shrink-0 mt-0.5" />
                <span>{{ $participantsLabel }}</span>
            </li>
        </ul>

        <div class="mt-4 pt-4 flex items-center justify-between gap-3 border-t border-charcoal/[0.06]">
            @if ($start <= 0)
                <span class="font-semibold text-base text-charcoal">Gratuit</span>
            @else
                <span class="font-semibold text-base text-charcoal">
                    <x-money :amount="$start" />
                </span>
            @endif

            @if ($remaining <= 0)
                <span class="ep-btn-card opacity-50 cursor-not-allowed">Complet</span>
            @elseif ($start <= 0)
                <span class="ep-btn-card">Réserver</span>
            @else
                <span class="ep-btn-card">Acheter un billet</span>
            @endif
        </div>
    </div>
</a>
