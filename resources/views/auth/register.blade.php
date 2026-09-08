<x-guest-layout>
    <div class="mb-7 text-center">
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Register hub title') }}</h1>
        <p class="mt-2 text-sm text-frost">{{ __('Register hub subtitle') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4">
        <a href="{{ route('register.participant') }}"
           class="group ep-card p-5 no-underline hover:border-brand/40 transition">
            <span class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-coral-muted text-coral">
                    <x-icon name="ticket" class="w-6 h-6" />
                </span>
                <span class="text-left">
                    <span class="block font-semibold text-charcoal dark:text-[#FAFAFA] group-hover:text-brand">{{ __('Register as participant') }}</span>
                    <span class="mt-1 block text-sm text-frost">{{ __('Register participant card hint') }}</span>
                </span>
            </span>
        </a>

        <a href="{{ route('register.organizer') }}"
           class="group ep-card p-5 no-underline hover:border-brand/40 transition">
            <span class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-muted text-violet">
                    <x-icon name="building-storefront" class="w-6 h-6" />
                </span>
                <span class="text-left">
                    <span class="block font-semibold text-charcoal dark:text-[#FAFAFA] group-hover:text-brand">{{ __('Register as organizer') }}</span>
                    <span class="mt-1 block text-sm text-frost">{{ __('Register organizer card hint') }}</span>
                </span>
            </span>
        </a>
    </div>

    <p class="mt-6 text-center text-sm text-frost">
        {{ __('Already registered?') }}
        <a href="{{ route('login') }}" class="font-semibold text-brand hover:text-brand-700">{{ __('Log in') }}</a>
    </p>
</x-guest-layout>
