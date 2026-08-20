<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-wider text-coral mb-2">{{ __('Password recovery') }}</p>
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Forgot your password?') }}</h1>
        <p class="mt-1.5 text-sm text-frost">{{ __('We will email you a reset link.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-frost">
                    <x-icon name="envelope" class="w-5 h-5" />
                </span>
                <x-text-input id="email" class="block w-full pl-10" type="email" name="email" :value="old('email')" required autofocus placeholder="vous@exemple.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            {{ __('Send reset link') }} <x-icon name="arrow-right" class="w-4 h-4" />
        </x-primary-button>

        <p class="text-center text-sm text-frost">
            <a href="{{ route('login') }}" class="font-semibold text-coral hover:text-brand-700">{{ __('Back to login') }}</a>
        </p>
    </form>
</x-guest-layout>
