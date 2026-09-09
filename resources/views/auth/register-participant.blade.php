<x-guest-layout>
    <div class="mb-7">
        <a href="{{ route('register') }}" class="inline-flex items-center gap-1 text-xs font-medium text-frost hover:text-brand mb-4 no-underline">
            <x-icon name="arrow-left" class="w-3.5 h-3.5" /> {{ __('Back to sign up choices') }}
        </a>
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Register participant title') }}</h1>
        <p class="mt-1.5 text-sm text-frost">{{ __('Register participant subtitle') }}</p>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('register.participant') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" class="block mt-1.5 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-password-input id="password" class="block mt-1.5 w-full" name="password" required autocomplete="new-password" />
            <p class="mt-1 text-xs text-frost">{{ __('Password min hint') }}</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            {{ __('Create participant account') }} <x-icon name="arrow-right" class="w-4 h-4" />
        </x-primary-button>
    </form>

    @include('partials.social-auth-buttons', ['context' => 'participant-register', 'class' => 'mt-5'])

    <p class="mt-5 text-center text-sm text-frost">
        {{ __('Organizer instead') }}
        <a href="{{ route('register.organizer') }}" class="font-semibold text-brand hover:text-brand-700">{{ __('Register as organizer') }}</a>
    </p>
</x-guest-layout>
