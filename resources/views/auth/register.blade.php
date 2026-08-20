<x-guest-layout>
    <div class="mb-7">
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Create an account') }}</h1>
        <p class="mt-1.5 text-sm text-frost">{{ __('Participant or organizer — same sign-up.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-frost">
                    <x-icon name="user-circle" class="w-5 h-5" />
                </span>
                <x-text-input id="name" class="block w-full pl-10" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="{{ __('Full name') }}" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-frost">
                    <x-icon name="envelope" class="w-5 h-5" />
                </span>
                <x-text-input id="email" class="block w-full pl-10" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="vous@exemple.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-password-input id="password" class="block mt-1.5 w-full"
                                name="password"
                                required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-password-input id="password_confirmation" class="block mt-1.5 w-full"
                                name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone (optional)')" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-frost">
                    <x-icon name="phone" class="w-5 h-5" />
                </span>
                <x-text-input id="phone" class="block w-full pl-10" type="tel" name="phone" :value="old('phone')" autocomplete="tel" placeholder="{{ config('eventpulse.phone.placeholder') }}" />
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label :value="__('I am signing up as')" />
            <div class="mt-2 grid grid-cols-2 gap-3" x-data="{ role: '{{ old('role', 'participant') }}' }">
                <label class="relative flex flex-col items-center gap-2 border-2 rounded-2xl px-3 py-4 cursor-pointer transition dark:border-white/10"
                       x-bind:class="role === 'participant' ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-slate-200 dark:border-white/10 hover:border-slate-300'">
                    <input type="radio" name="role" value="participant" x-model="role" class="sr-only" {{ old('role', 'participant') === 'participant' ? 'checked' : '' }} />
                    <x-icon name="ticket" class="w-6 h-6" x-bind:class="role === 'participant' ? 'text-brand' : 'text-slate-400'" />
                    <span class="text-sm font-semibold" x-bind:class="role === 'participant' ? 'text-brand-700' : 'text-slate-600 dark:text-frost'">{{ __('Participant') }}</span>
                </label>
                <label class="relative flex flex-col items-center gap-2 border-2 rounded-2xl px-3 py-4 cursor-pointer transition dark:border-white/10"
                       x-bind:class="role === 'organizer' ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-slate-200 dark:border-white/10 hover:border-slate-300'">
                    <input type="radio" name="role" value="organizer" x-model="role" class="sr-only" {{ old('role') === 'organizer' ? 'checked' : '' }} />
                    <x-icon name="building-storefront" class="w-6 h-6" x-bind:class="role === 'organizer' ? 'text-brand' : 'text-slate-400'" />
                    <span class="text-sm font-semibold" x-bind:class="role === 'organizer' ? 'text-brand-700' : 'text-slate-600 dark:text-frost'">{{ __('Organizer') }}</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            {{ __('Create my account') }} <x-icon name="arrow-right" class="w-4 h-4" />
        </x-primary-button>

        <p class="text-center text-sm text-frost">
            {{ __('Already registered?') }}
            <a href="{{ route('login') }}" class="font-semibold text-brand hover:text-brand-700">{{ __('Log in') }}</a>
        </p>
    </form>
</x-guest-layout>
