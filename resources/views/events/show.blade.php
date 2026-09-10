<x-app-layout
    :seo-title="$seo['title']"
    :seo-description="$seo['description']"
    :seo-url="$seo['url']"
    :seo-image="$seo['image']"
    :seo-type="$seo['type']"
>
    @php $remaining = $event->remainingSeats(); @endphp

    {{-- Hero --}}
    <div class="relative h-72 sm:h-96 bg-charcoal overflow-hidden">
        @if ($event->image_path)
            <x-event-image :event="$event" :alt="$event->title" variant="hero"
                           class="absolute inset-0 w-full h-full object-cover" width="1200" height="675" />
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-charcoal">
                <img src="{{ asset('images/brand/mark.svg') }}" alt="" class="w-20 h-20 opacity-30">
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-charcoal via-charcoal/50 to-transparent"></div>

        <div class="absolute inset-0 flex flex-col justify-between max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <a href="{{ route('events.index') }}"
               class="inline-flex items-center gap-1.5 self-start px-3.5 py-2 rounded-lg bg-white/10 backdrop-blur text-white text-sm font-medium hover:bg-white/20 transition">
                <x-icon name="arrow-left" class="w-4 h-4" /> Catalogue
            </a>

            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <x-category-badge :category="$event->category ?? 'other'" />
                    @if ($event->status === 'cancelled')
                        <span class="inline-flex px-2.5 py-1 rounded-md bg-coral text-white text-xs font-bold uppercase">Annulé</span>
                    @endif
                </div>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-white tracking-tight max-w-3xl">{{ $event->title }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-frost">
                    <span class="flex items-center gap-1.5"><x-icon name="calendar" class="w-4 h-4" /> {{ $event->event_date->translatedFormat('l d F Y à H:i') }}</span>
                    <span class="flex items-center gap-1.5"><x-icon name="map-pin" class="w-4 h-4" /> {{ $event->location }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="py-10 pb-16">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 px-4 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            {{-- Contenu --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="ep-card p-6 sm:p-8">
                    <h2 class="font-display font-bold text-lg text-charcoal mb-4">À propos</h2>
                    <div class="prose prose-slate max-w-none text-frost whitespace-pre-line leading-relaxed">{{ $event->description }}</div>
                </div>

                <div class="ep-card p-6 sm:p-8 flex items-center gap-4">
                    <span class="flex items-center justify-center w-11 h-11 rounded-lg bg-charcoal text-white font-bold text-base shrink-0">
                        {{ Str::of($event->user->name)->substr(0, 1) }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-frost font-medium uppercase tracking-wide">Organisé par</p>
                        <p class="font-display font-bold text-charcoal">{{ $event->user->name }}</p>
                        @if ($event->user->bank_account_number && $event->acceptsPaymentMethod('card'))
                            <p class="mt-1 text-xs text-frost">
                                Carte / virement · {{ $event->user->bank_name }} · {{ $event->user->bank_account_number }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Réservation --}}
            @php
                $purchasableTypes = $event->purchasableTicketTypes();
                $isFreeEvent = $event->isFreeEvent();
            @endphp
            <div class="lg:col-span-1">
                <div class="ep-card border-l-4 border-l-coral p-5 sm:p-6 space-y-4">
                    <div class="text-sm">
                        <p class="flex items-center gap-2 text-frost">
                            <x-icon name="users" class="w-4 h-4 shrink-0 text-coral" />
                            {{ $remaining }} / {{ $event->capacity }} places disponibles
                        </p>
                        <div class="mt-2 h-1.5 rounded-full bg-charcoal/[0.06] overflow-hidden">
                            @php $filledPct = $event->capacity > 0 ? min(100, round((($event->capacity - $remaining) / $event->capacity) * 100)) : 0; @endphp
                            <div class="h-full rounded-full bg-coral" style="width: {{ $filledPct }}%"></div>
                        </div>
                    </div>

                    @if ($event->status === 'cancelled')
                        <div class="flex items-center justify-center gap-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 p-4 text-sm font-semibold">
                            <x-icon name="x-circle" class="w-5 h-5 shrink-0" /> Événement annulé
                        </div>
                    @elseif (! $event->isUpcoming())
                        <div class="rounded-xl bg-slate-100 text-slate-500 p-4 text-sm text-center font-medium">
                            Cet événement est terminé.
                        </div>
                    @elseif ($remaining <= 0)
                        <div class="flex items-center justify-center gap-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 p-4 text-sm font-bold">
                            Complet !
                        </div>
                    @elseif (auth()->check() && auth()->id() === $event->user_id)
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-center space-y-3">
                            <p class="text-sm text-slate-600">{{ __('Organizer own event hint') }}</p>
                            <a href="{{ route('organizer.events.index') }}"
                               class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-ink text-white font-semibold px-4 py-3 text-sm hover:bg-slate-800 transition">
                                {{ __('Manage my events') }}
                            </a>
                        </div>
                    @elseif ($alreadyBooked)
                        <div class="flex items-center gap-2 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 p-3 text-sm">
                            <x-icon name="ticket" class="w-4 h-4 shrink-0" />
                            <span>
                                {{ $isFreeEvent ? __('Already booked free') : __('Already booked paid') }}
                                <a href="{{ route('tickets.index') }}" class="underline font-semibold">{{ __('View my tickets') }}</a>
                            </span>
                        </div>
                    @elseif ($event->ticketTypes->isEmpty())
                        <div class="rounded-xl bg-amber-50 border border-amber-100 text-amber-800 p-4 text-sm text-center">
                            {{ __('No ticket types configured') }}
                        </div>
                    @elseif ($purchasableTypes->isEmpty())
                        <div class="rounded-xl bg-amber-50 border border-amber-100 text-amber-800 p-4 text-sm text-center">
                            {{ __('No ticket types on sale now') }}
                        </div>
                    @else
                        <a href="{{ route('tickets.choose', $event) }}" class="ep-btn w-full justify-center py-3 no-underline">
                            <x-icon name="ticket" class="w-4 h-4" />
                            {{ $isFreeEvent ? __('Catalog book free') : __('Catalog book paid') }}
                        </a>
                        <p class="text-[10px] text-center text-frost">
                            {{ $isFreeEvent ? __('Checkout free hint') : __('Checkout paid hint') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
