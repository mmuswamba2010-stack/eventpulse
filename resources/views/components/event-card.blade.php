@props(['event'])

@php
    $remaining = $event->remainingSeats();
    $isFullyFree = $event->isFreeEvent();
    $sold = $event->soldTicketsCount();
    $participantsLabel = match (true) {
        $sold === 0 => __('No registrations yet'),
        $sold === 1 => __('One participant'),
        default => __('Participants count', ['count' => number_format($sold, 0, ',', ' ')]),
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
    $bookUrl = route('tickets.choose', $event);
@endphp

<div {{ $attributes->class(['group ep-event-card flex flex-col h-full']) }}>
    <a href="{{ route('events.show', $event->slug) }}" class="flex flex-col flex-1 no-underline">
        <div class="relative h-44 bg-[#ECECEE] dark:bg-[#252525] overflow-hidden">
            @if ($event->image_path)
                <x-event-image :event="$event" :alt="$event->title" variant="card"
                               class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105" width="640" height="352" />
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
        </div>

        <div class="flex flex-1 flex-col p-5">
            <h3 class="font-semibold text-[16px] leading-snug text-charcoal dark:text-[#FAFAFA] line-clamp-2">
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
        </div>
    </a>

    <div class="px-5 pb-5 pt-0 mt-auto">
        <div class="pt-4 border-t border-charcoal/[0.06] dark:border-white/10">
            @if ($remaining <= 0)
                <span class="ep-btn-card opacity-50 cursor-not-allowed">{{ __('Sold out') }}</span>
            @elseif ($isFullyFree)
                <a href="{{ $bookUrl }}" class="ep-btn-card no-underline">{{ __('Catalog book free') }}</a>
            @else
                <a href="{{ $bookUrl }}" class="ep-btn-card no-underline">{{ __('Catalog book paid') }}</a>
            @endif
        </div>
    </div>
</div>
