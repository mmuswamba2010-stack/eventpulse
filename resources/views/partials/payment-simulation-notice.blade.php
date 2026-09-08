@if (config('eventpulse.payment_simulation', true))
    <div class="rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-xs text-amber-900 dark:text-amber-200">
        <p class="font-semibold">{{ __('Payment simulation notice title') }}</p>
        <p class="mt-0.5 text-amber-800/90 dark:text-amber-200/80">{{ __('Payment simulation notice body') }}</p>
    </div>
@else
    <div class="rounded-xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-xs text-emerald-900 dark:text-emerald-200">
        <p class="font-semibold flex items-center gap-1.5">
            <x-icon name="shield-check" class="w-4 h-4 shrink-0" />
            {{ __('Payment real notice title') }}
        </p>
        <p class="mt-0.5 text-emerald-800/90 dark:text-emerald-200/80">{{ __('Payment real notice body') }}</p>
    </div>
@endif
