<x-guest-layout>
    <div class="mb-8 text-center">
        <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-300 mb-5">
            <x-icon name="check-circle" class="w-8 h-8" />
        </span>
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Password reset successful') }}</h1>
        <p class="mt-3 text-sm text-frost leading-relaxed max-w-sm mx-auto">{{ __('Your password has been reset. You can now sign in with your new password.') }}</p>
    </div>

    <a href="{{ route('login') }}" class="ep-btn w-full py-3 text-center justify-center">
        {{ __('Back to login') }} <x-icon name="arrow-right" class="w-4 h-4" />
    </a>
</x-guest-layout>
