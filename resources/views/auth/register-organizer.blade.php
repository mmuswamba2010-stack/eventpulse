<x-guest-layout>
    <div class="mb-7">
        <a href="{{ route('register') }}" class="inline-flex items-center gap-1 text-xs font-medium text-frost hover:text-brand mb-4 no-underline">
            <x-icon name="arrow-left" class="w-3.5 h-3.5" /> {{ __('Back to sign up choices') }}
        </a>
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Register organizer title') }}</h1>
        <p class="mt-1.5 text-sm text-frost">{{ __('Register organizer subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('register.organizer') }}" class="space-y-4">
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
            <x-input-label for="phone" :value="__('Mobile money number organizer')" />
            <x-text-input id="phone" class="block mt-1.5 w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="{{ config('eventpulse.phone.placeholder') }}" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-password-input id="password" class="block mt-1.5 w-full" name="password" required autocomplete="new-password" />
            <p class="mt-1 text-xs text-frost">{{ __('Password min hint') }}</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label class="flex items-start gap-3 cursor-pointer rounded-2xl border border-charcoal/10 dark:border-white/10 bg-cream/60 dark:bg-[#141414] p-4">
            <input type="checkbox" name="accept_organizer_terms" value="1" class="mt-1 rounded border-charcoal/20 text-brand focus:ring-brand/30" {{ old('accept_organizer_terms') ? 'checked' : '' }} required>
            <span class="text-sm text-charcoal dark:text-[#FAFAFA] leading-relaxed">
                {!! __('Organizer terms acceptance label html', ['url' => route('legal.organizer-terms')]) !!}
            </span>
        </label>
        <x-input-error :messages="$errors->get('accept_organizer_terms')" class="mt-2" />

        <x-primary-button class="w-full py-3">
            {{ __('Create organizer account') }} <x-icon name="arrow-right" class="w-4 h-4" />
        </x-primary-button>
    </form>

    <p class="mt-5 text-center text-sm text-frost">
        {{ __('Participant instead') }}
        <a href="{{ route('register.participant') }}" class="font-semibold text-brand hover:text-brand-700">{{ __('Register as participant') }}</a>
    </p>
</x-guest-layout>
