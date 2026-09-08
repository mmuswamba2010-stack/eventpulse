<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA] tracking-tight">
            {{ __('Payment status title') }}
        </h2>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8 px-4">
            <div class="ep-card p-6 sm:p-8 space-y-5">
                <div class="text-center">
                    @if ($payment->isSucceeded())
                        <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 mb-3">
                            <x-icon name="check-circle" class="w-8 h-8" />
                        </span>
                        <p class="font-bold text-lg text-emerald-700 dark:text-emerald-400">{{ __('Payment succeeded') }}</p>
                    @elseif ($payment->status === 'failed')
                        <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 mb-3">
                            <x-icon name="x-circle" class="w-8 h-8" />
                        </span>
                        <p class="font-bold text-lg text-rose-700 dark:text-rose-400">{{ __('Payment failed') }}</p>
                    @else
                        <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 mb-3 animate-pulse">
                            <x-icon name="clock" class="w-8 h-8" />
                        </span>
                        <p class="font-bold text-lg text-amber-700 dark:text-amber-400">{{ __('Payment pending') }}</p>
                    @endif
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-frost">{{ __('Payment reference') }}</dt>
                        <dd class="font-mono font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $payment->reference }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-frost">{{ __('Payment amount') }}</dt>
                        <dd class="font-semibold text-charcoal dark:text-[#FAFAFA]">
                            @if ($payment->purpose === \App\Models\Payment::PURPOSE_PUBLICATION)
                                <x-money :amount="$payment->amount" primary="usd" :free="false" />
                            @else
                                <x-money :amount="$payment->amount" />
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($payment->isPending())
                    <p class="text-xs text-frost text-center">{{ __('Payment pending mobile money confirmation.') }}</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ url()->current() }}"
                           class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold">
                            {{ __('Refresh status') }}
                        </a>
                        <a href="{{ route('tickets.index') }}"
                           class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-charcoal/10 text-sm font-semibold text-charcoal dark:text-[#FAFAFA]">
                            {{ __('My tickets') }}
                        </a>
                    </div>
                @else
                    <a href="{{ $payment->purpose === 'publication_fee' ? route('organizer.events.index') : route('tickets.index') }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold">
                        {{ __('Back') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
