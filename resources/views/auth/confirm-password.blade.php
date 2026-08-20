<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-wider text-coral mb-2">{{ __('Confirmation') }}</p>
        <h1 class="font-display text-xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Confirm your identity') }}</h1>
        <p class="mt-1.5 text-sm text-frost">{{ __('Enter your password to continue.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-password-input id="password" class="block mt-1.5 w-full"
                            name="password"
                            required autocomplete="current-password" autofocus />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            {{ __('Confirm') }}
        </x-primary-button>
    </form>
</x-guest-layout>
