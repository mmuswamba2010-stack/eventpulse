<x-app-layout>
    <div class="py-10 pb-16">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8 px-4">
            <a href="{{ route('events.show', $event->slug) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-frost hover:text-brand no-underline mb-6">
                <x-icon name="arrow-left" class="w-4 h-4" /> {{ __('Back to event') }}
            </a>

            <div class="mb-8">
                <h1 class="font-display text-2xl sm:text-3xl font-bold text-charcoal dark:text-[#FAFAFA]">
                    {{ __('Choose your ticket') }}
                </h1>
                <p class="mt-2 text-sm text-frost">{{ $event->title }}</p>
                <p class="mt-1 text-xs text-frost">
                    {{ $event->event_date->locale(app()->getLocale())->translatedFormat('D d M · H\hi') }}
                    · {{ $event->location }}
                </p>
            </div>

            @if (session('error'))
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-6 rounded-xl border border-sky-200 bg-sky-50 text-sky-700 px-4 py-3 text-sm">
                    {{ session('info') }}
                </div>
            @endif

            @if ($event->status === 'cancelled')
                <div class="ep-card p-6 text-center text-sm text-rose-700">
                    {{ __('Event cancelled') }}
                </div>
            @elseif (auth()->check() && auth()->id() === $event->user_id)
                <div class="ep-card p-6 text-center space-y-4">
                    <p class="text-sm text-frost">{{ __('Organizer own event hint') }}</p>
                    <a href="{{ route('organizer.events.index') }}" class="ep-btn w-full justify-center">
                        {{ __('Manage my events') }}
                    </a>
                </div>
            @elseif ($alreadyBooked)
                <div class="ep-card p-6 text-center space-y-4">
                    <p class="text-sm text-frost">
                        {{ $isFreeEvent ? __('Already booked free') : __('Already booked paid') }}
                    </p>
                    <a href="{{ route('tickets.index') }}" class="ep-btn w-full justify-center">
                        {{ __('View my tickets') }}
                    </a>
                </div>
            @elseif ($purchasableTypes->isEmpty())
                <div class="ep-card p-6 text-center text-sm text-amber-800 bg-amber-50">
                    {{ __('No ticket types on sale now') }}
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($types as $type)
                        @php
                            $typeRemaining = $type->remainingSeats();
                            $purchasable = $type->isPurchasable();
                        @endphp
                        <div @class([
                            'ep-card p-5 sm:p-6',
                            'opacity-60' => ! $purchasable,
                        ])>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="min-w-0">
                                    <h2 class="font-display text-xl font-bold text-charcoal dark:text-[#FAFAFA]">
                                        {{ $type->name }}
                                    </h2>
                                    <p class="mt-1 text-base font-semibold text-charcoal dark:text-[#FAFAFA]">
                                        {{ \App\Support\Money::formatEventPriceLabel($type->price, (float) $type->price <= 0) }}
                                    </p>
                                    <p class="mt-2 text-sm text-frost">
                                        {{ __('Seats available count', ['count' => $typeRemaining]) }}
                                    </p>
                                    @unless ($purchasable)
                                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-frost">
                                            {{ $type->saleStatusLabel() }}
                                        </p>
                                    @endunless
                                </div>

                                @if ($purchasable)
                                    <form method="POST" action="{{ route('tickets.store', $event) }}" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="ticket_type_id" value="{{ $type->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="ep-btn w-full sm:w-auto justify-center min-w-[8rem] py-3">
                                            {{ __('Choose ticket') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-xl border border-charcoal/[0.08] px-5 py-3 text-sm font-semibold text-frost shrink-0">
                                        {{ __('Unavailable') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
