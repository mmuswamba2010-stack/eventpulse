<x-app-layout>
    <div class="relative overflow-hidden bg-ink ep-hero">

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <a href="{{ route('organizer.events.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-300 hover:text-white transition mb-8">
                <x-icon name="arrow-left" class="w-4 h-4" /> {{ __('Back to my events') }}
            </a>

            <div class="text-center mb-10">
                @if (config('eventpulse.payment_simulation', true))
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-400/20 border border-amber-300/40 text-sm font-semibold text-amber-100 mb-4">
                        <x-icon name="shield-check" class="w-4 h-4" /> {{ __('Secure payment simulation') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-emerald-500/25 border border-emerald-300/40 text-sm font-semibold text-white mb-4 shadow-sm">
                        <x-icon name="shield-check" class="w-4 h-4" /> {{ __('Secure payment') }}
                    </span>
                @endif
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    {{ __('Publish event heading', ['title' => $event->title]) }}
                </h1>
                <p class="mt-3 text-slate-300 max-w-xl mx-auto">
                    {{ __('Publication payment intro integrator') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden">
                        <div class="relative h-32 bg-gradient-to-br bg-brand">
                            @if ($event->image_path)
                                <x-event-image :event="$event" :alt="$event->title" variant="thumb"
                                               class="w-full h-full object-cover" width="640" height="352" />
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <x-icon name="photo" class="w-10 h-10 text-white/50" />
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                        </div>
                        <div class="p-5">
                            <h2 class="font-bold text-white line-clamp-1">{{ $event->title }}</h2>

                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="calendar" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span>{{ $event->event_date->translatedFormat('d/m/Y à H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="map-pin" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span class="truncate">{{ $event->location }}</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="users" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span>{{ __('Seats count', ['count' => $event->capacity]) }}</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="ticket" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span><x-money :amount="$event->price" primary="usd" :free="false" on-dark /> {{ __('Per ticket') }}</span>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="rounded-3xl bg-gradient-to-br bg-brand hover:bg-brand-700 p-6 text-white ">
                        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-100">
                            <x-icon name="banknotes" class="w-4 h-4" /> {{ __('Publication fee label') }}
                        </p>
                        <p class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight">
                            <x-money :amount="$event->publication_fee" primary="usd" :free="false" on-dark />
                        </p>
                        <p class="mt-2 text-sm text-brand-100/90">{{ __('Publication fee once integrator') }}</p>
                    </div>
                </div>

                <div class="lg:col-span-3 ep-card rounded-3xl p-6 sm:p-8 shadow-2xl">
                    @include('partials.payment-simulation-notice')

                    @unless (config('eventpulse.payment_simulation', true))
                        <div class="rounded-2xl border border-brand/20 bg-brand-50/60 dark:bg-brand-950/40 dark:border-brand-700/50 px-4 py-4 text-sm mb-6">
                            <p class="flex items-center gap-2 font-semibold text-charcoal dark:text-[#FAFAFA]">
                                <x-icon name="credit-card" class="w-4 h-4 text-brand shrink-0" />
                                {{ __('Publication integrator coming soon title') }}
                            </p>
                            <p class="mt-2 text-frost">{{ __('Publication integrator coming soon body') }}</p>
                        </div>
                    @endunless

                    <form method="POST" action="{{ route('organizer.events.pay.process', $event) }}" class="space-y-6 mt-4">
                        @csrf

                        <div class="rounded-2xl border border-charcoal/10 dark:border-white/10 bg-charcoal/[0.03] dark:bg-white/5 px-4 py-4">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide">
                                <x-icon name="credit-card" class="w-4 h-4 text-brand" /> {{ __('Publication payment section') }}
                            </h3>
                            <p class="mt-2 text-sm text-frost">{{ __('Publication payment integrator hint') }}</p>
                            <p class="mt-3 text-sm font-semibold text-charcoal dark:text-[#FAFAFA]">
                                {{ __('Amount label') }}
                                <x-money :amount="$event->publication_fee" primary="usd" :free="false" class="ml-1" />
                            </p>
                        </div>

                        <x-primary-button type="submit" class="w-full justify-center !py-3.5">
                            <x-icon name="lock-closed" class="w-4 h-4" />
                            {{ __('Pay and publish fee') }}
                            <x-money :amount="$event->publication_fee" primary="usd" :free="false" on-dark class="ml-1" />
                        </x-primary-button>

                        @if (config('eventpulse.payment_simulation', true))
                            <p class="flex items-center justify-center gap-1.5 text-xs text-frost">
                                <x-icon name="shield-check" class="w-3.5 h-3.5 text-amber-500" />
                                {{ __('Simulated transaction notice') }}
                            </p>
                        @else
                            <p class="flex items-center justify-center gap-1.5 text-xs text-frost text-center">
                                <x-icon name="clock" class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                                {{ __('Payment real pending notice integrator') }}
                            </p>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
