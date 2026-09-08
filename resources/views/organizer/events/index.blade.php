<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="flex items-center gap-2 font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA] tracking-tight">
                    <x-icon name="calendar" class="w-7 h-7 text-brand" />
                    {{ __('My events') }}
                </h2>
                <p class="mt-1 text-sm text-frost">{{ __('My events subtitle') }}</p>
            </div>
            <a href="{{ route('organizer.events.create') }}"
               class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r bg-brand hover:bg-brand-700 text-white text-sm font-semibold rounded-full  hover:brightness-110 transition self-start">
                <x-icon name="plus" class="w-4 h-4" /> {{ __('New event') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            @if ($events->isEmpty())
                <div class="ep-card shadow-sm p-14 text-center">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-charcoal/[0.04] dark:bg-white/5 text-frost mb-4">
                        <x-icon name="calendar" class="w-7 h-7" />
                    </span>
                    <p class="text-frost font-medium">{{ __('No events yet organizer') }}</p>
                    <a href="{{ route('organizer.events.create') }}"
                       class="inline-flex items-center gap-1.5 mt-4 px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-700 text-white text-sm font-semibold  hover:brightness-110 transition">
                        {{ __('Create first event') }} <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($events as $event)
                        @php $pct = $event->capacity > 0 ? min(100, round(($event->sold_count / $event->capacity) * 100)) : 0; @endphp
                        <div class="ep-event-card hover:shadow-lg transition overflow-hidden flex flex-col">
                            <div class="relative h-36 bg-gradient-to-br bg-brand overflow-hidden">
                                @if ($event->image_path)
                                    <x-event-image :event="$event" :alt="$event->title" variant="thumb"
                                                   class="w-full h-full object-cover" width="640" height="352" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <x-icon name="photo" class="w-10 h-10 text-white/50" />
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/50 to-transparent"></div>
                                <span class="absolute top-3 right-3 text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur
                                    {{ $event->status === 'published' ? 'bg-emerald-500/90 text-white' : ($event->status === 'draft' ? 'bg-amber-500/90 text-white' : 'bg-rose-500/90 text-white') }}">
                                    @php
                                        $statusLabels = [
                                            'published' => __('Published'),
                                            'draft' => __('Draft'),
                                            'cancelled' => __('Cancelled'),
                                        ];
                                    @endphp
                                    {{ $event->needsPayment() ? __('Pending') : ($statusLabels[$event->status] ?? $event->status) }}
                                </span>
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                @if ($event->isPublished())
                                    <a href="{{ route('events.show', $event->slug) }}" class="font-bold text-charcoal dark:text-[#FAFAFA] hover:text-brand transition line-clamp-1">
                                        {{ $event->title }}
                                    </a>
                                @else
                                    <a href="{{ route('organizer.events.edit', $event) }}" class="font-bold text-charcoal dark:text-[#FAFAFA] hover:text-brand transition line-clamp-1">
                                        {{ $event->title }}
                                    </a>
                                @endif
                                <p class="flex items-center gap-1.5 text-sm text-frost mt-2">
                                    <x-icon name="map-pin" class="w-4 h-4 shrink-0 text-frost" /> <span class="truncate">{{ $event->location }}</span>
                                </p>
                                <p class="flex items-center gap-1.5 text-sm text-frost mt-1">
                                    <x-icon name="calendar" class="w-4 h-4 shrink-0 text-frost" /> {{ $event->event_date->format('d/m/Y H:i') }}
                                </p>

                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-frost mb-1.5">
                                        <span>{{ __('Sold progress', ['sold' => $event->sold_count, 'capacity' => $event->capacity]) }}</span>
                                        <span class="font-semibold">
                                            <x-money :amount="$event->price" primary="usd" :free="false" />
                                        </span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-charcoal/[0.06] dark:bg-white/10 overflow-hidden">
                                        <div class="h-full rounded-xl bg-brand" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>

                                @if ($event->needsPayment())
                                    <a href="{{ route('organizer.events.pay', $event) }}"
                                       class="mt-4 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-gold hover:bg-gold-700 text-ink text-xs font-bold rounded-xl transition">
                                        <x-icon name="banknotes" class="w-3.5 h-3.5" /> {{ __('Pay and publish') }}
                                    </a>
                                @elseif ($event->is_paid && \App\Models\Event::requiresPublicationPayment())
                                    <p class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        <x-icon name="check-badge" class="w-3.5 h-3.5 shrink-0" />
                                        {{ __('Publication fee paid before') }}<x-money :amount="$event->publication_fee ?? \App\Models\Event::publicationFee()" primary="usd" :free="false" />{{ __('Publication fee paid after') }}
                                        @if ($event->payment_method)
                                            · {{ $event->payment_method === 'card' ? __('Payment method card') : __('Payment method mobile_money') }}
                                        @endif
                                        @if ($event->paid_at)
                                            · {{ $event->paid_at->format('d/m/Y') }}
                                        @endif
                                    </p>
                                @endif

                                <div class="mt-4 pt-4 border-t border-charcoal/5 dark:border-white/5 flex items-center gap-2">
                                    <a href="{{ route('organizer.events.edit', $event) }}"
                                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-charcoal/[0.04] dark:bg-white/5 hover:bg-charcoal/[0.08] dark:hover:bg-white/10 text-charcoal dark:text-[#FAFAFA] text-xs font-bold rounded-full transition">
                                        <x-icon name="pencil-square" class="w-3.5 h-3.5" /> {{ __('Edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" class="flex-1"
                                          onsubmit="return confirm(@js(__('Delete event confirm')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-950/50 text-rose-600 dark:text-rose-400 text-xs font-bold rounded-full transition">
                                            <x-icon name="x-circle" class="w-3.5 h-3.5" /> {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
