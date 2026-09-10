@php
    $organizer = $event->user;
    $defaultPayment = old('payment_method', $paymentMethod ?? ($acceptedPayments[0] ?? 'card'));
@endphp

<x-guest-layout>
    <div class="mb-6">
        <a href="{{ route('tickets.choose', $event) }}"
           class="inline-flex items-center gap-1 text-xs font-medium text-frost hover:text-brand mb-4 no-underline">
            <x-icon name="arrow-left" class="w-3.5 h-3.5" /> {{ __('Back to ticket choice') }}
        </a>
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">
            {{ $isFreeTicket ? __('Free checkout confirm title') : __('Paid checkout confirm title') }}
        </h1>
        <p class="mt-1.5 text-sm text-frost">
            {{ $isFreeTicket ? __('Free checkout confirm subtitle') : __('Paid checkout confirm subtitle') }}
        </p>
    </div>

    @include('partials.checkout-alerts')

    <div class="ep-card p-5 space-y-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-frost">{{ $event->title }}</p>
            <p class="mt-1 text-sm text-charcoal dark:text-[#FAFAFA]">{{ $event->event_date->translatedFormat('d/m/Y · H\hi') }} · {{ $event->location }}</p>
        </div>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="text-frost">{{ __('Ticket type label') }}</dt>
                <dd class="font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $ticketType->name }}</dd>
            </div>
            <div>
                <dt class="text-frost">{{ __('Quantity label') }}</dt>
                <dd class="font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $quantity }}</dd>
            </div>
            <div class="col-span-2">
                <dt class="text-frost">{{ __('Total label') }}</dt>
                <dd class="font-semibold text-charcoal dark:text-[#FAFAFA]">
                    <x-money :amount="$ticketType->price * $quantity" primary="usd" :free="$isFreeTicket" />
                </dd>
            </div>
        </dl>
    </div>

    @if (! $isFreeTicket)
        @include('partials.payment-simulation-notice')

        <div class="ep-card p-5 mb-6 space-y-4"
             x-data="{ payMethod: @js($defaultPayment) }">
            <div>
                <x-input-label value="{{ __('Payment method label') }}" class="text-xs" />
                <div class="mt-2 grid grid-cols-2 gap-2">
                    @foreach ($acceptedPayments as $method)
                        <label class="flex flex-col items-center gap-1.5 border rounded-xl px-2 py-3 cursor-pointer transition text-center"
                               x-bind:class="payMethod === '{{ $method }}' ? 'border-coral bg-coral-muted/40' : 'border-charcoal/[0.08] dark:border-white/10'">
                            <input type="radio" name="payment_method" value="{{ $method }}"
                                   x-model="payMethod" class="sr-only">
                            @if ($method === 'card')
                                <x-icon name="credit-card" class="w-5 h-5 text-coral" />
                                <span class="text-xs font-semibold text-charcoal dark:text-[#FAFAFA]">{{ __('Card payment') }}</span>
                            @else
                                <x-icon name="banknotes" class="w-5 h-5 text-coral" />
                                <span class="text-xs font-semibold text-charcoal dark:text-[#FAFAFA]">{{ __('Cash payment') }}</span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>

            <div x-show="payMethod === 'card'" x-cloak class="rounded-xl border border-charcoal/[0.08] dark:border-white/10 bg-cream/50 dark:bg-[#141414] p-4 space-y-2">
                @if ($organizer->bank_account_number)
                    <div class="rounded-lg bg-white dark:bg-[#1A1A1A] px-3 py-2.5 text-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-coral">{{ __('Bank transfer organizer details') }}</p>
                        <p class="font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $organizer->bank_account_holder ?: $organizer->name }}</p>
                        <p class="mt-0.5 text-xs text-frost">{{ $organizer->bank_name }}</p>
                        <p class="mt-0.5 font-mono text-sm text-charcoal dark:text-[#FAFAFA]">{{ $organizer->bank_account_number }}</p>
                    </div>
                    <p class="text-xs text-frost">{{ __('Bank transfer checkout hint') }}</p>
                @else
                    <p class="text-xs text-frost">{{ __('Bank details missing hint') }}</p>
                @endif
            </div>

            <p x-show="payMethod === 'cash'" x-cloak class="text-xs text-frost bg-cream dark:bg-[#141414] rounded-lg px-3 py-2.5">
                {{ __('Cash checkout hint') }}
            </p>

            @auth
                <form method="POST" action="{{ route('tickets.checkout.complete', $event) }}" class="pt-1">
                    @csrf
                    <input type="hidden" name="payment_method" x-bind:value="payMethod">
                    <x-primary-button class="w-full py-3">
                        {{ __('Paid checkout confirm button') }} <x-icon name="arrow-right" class="w-4 h-4" />
                    </x-primary-button>
                </form>
            @else
                <form method="POST" action="{{ route('tickets.checkout.payment', $event) }}" class="pt-1">
                    @csrf
                    <input type="hidden" name="payment_method" x-bind:value="payMethod">
                    <button type="submit" class="ep-btn w-full justify-center py-3">
                        {{ __('Paid checkout save payment button') }} <x-icon name="arrow-right" class="w-4 h-4" />
                    </button>
                </form>
            @endauth
        </div>
    @endif

    @auth
        @if ($isFreeTicket)
            <form method="POST" action="{{ route('tickets.checkout.complete', $event) }}">
                @csrf
                <x-primary-button class="w-full py-3">
                    {{ __('Free checkout confirm button') }} <x-icon name="arrow-right" class="w-4 h-4" />
                </x-primary-button>
            </form>
        @endif
    @else
        @if ($isFreeTicket)
            @include('partials.checkout-auth-prompt', ['prompt' => __('Free checkout login required')])
        @elseif ($paymentMethod)
            @include('partials.checkout-auth-prompt', ['prompt' => __('Paid checkout login required')])
        @else
            <p class="text-sm text-frost text-center">{{ __('Paid checkout choose payment first') }}</p>
            <a href="{{ route('events.index') }}" class="block text-center text-sm font-medium text-frost hover:text-brand no-underline mt-4">
                {{ __('Back to home') }}
            </a>
        @endif
    @endauth
</x-guest-layout>
