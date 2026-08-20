<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-wider text-coral mb-2">{{ __('Verification') }}</p>
        <h1 class="font-display text-xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Confirm your email') }}</h1>
        <p class="mt-2 text-sm text-frost leading-relaxed">
            {{ __('Click the link in your email to activate your account. Didn\'t receive it? Request a new one below.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 flex items-center gap-2 font-medium text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl px-4 py-3">
            <x-icon name="check-circle" class="w-4 h-4 shrink-0" />
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full py-3">
                {{ __('Resend verification email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-center text-sm font-semibold text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] py-2">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
