<x-guest-layout>
    <div class="mb-7">
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Log in to Event Pulse') }}</h1>
        <p class="mt-1.5 text-sm text-frost">{{ __('Login subtitle split') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif
    @if (session('info'))
        <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">{{ session('info') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-frost">
                    <x-icon name="envelope" class="w-5 h-5" />
                </span>
                <x-text-input id="email" class="block w-full pl-10" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="vous@exemple.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 text-frost">
                    <x-icon name="lock-closed" class="w-5 h-5" />
                </span>
                <x-password-input id="password" class="block w-full pl-10"
                                name="password"
                                required autocomplete="current-password" placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded-md border-slate-300 text-brand focus:ring-brand" name="remember">
                <span class="text-sm text-frost">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-brand hover:text-brand-700" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full py-3">
            {{ __('Log in to Event Pulse') }} <x-icon name="arrow-right" class="w-4 h-4" />
        </x-primary-button>

        @include('partials.social-auth-buttons', ['context' => 'login'])

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
            <a href="{{ route('register.participant') }}" class="ep-btn-outline text-sm py-2.5 justify-center no-underline">
                {{ __('Create participant account') }}
            </a>
            <a href="{{ route('register.organizer') }}" class="ep-btn-outline text-sm py-2.5 justify-center no-underline">
                {{ __('Register as organizer') }}
            </a>
        </div>
    </form>

    <a href="{{ route('events.index') }}" class="mt-6 block text-center text-sm font-medium text-frost hover:text-brand no-underline">
        {{ __('Back to home') }}
    </a>
</x-guest-layout>
