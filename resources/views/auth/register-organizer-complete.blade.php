<x-guest-layout>
    <div class="mb-7">
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Register organizer complete title') }}</h1>
        <p class="mt-1.5 text-sm text-frost">{{ __('Register organizer complete subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('register.organizer.complete.store') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" class="block mt-1.5 w-full" type="tel" name="phone" :value="old('phone', auth()->user()->phone)" required autocomplete="tel" placeholder="{{ config('eventpulse.phone.placeholder') }}" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <label class="flex items-start gap-3 cursor-pointer rounded-2xl border border-charcoal/10 dark:border-white/10 bg-cream/60 dark:bg-[#141414] p-4">
            <input type="checkbox" name="accept_organizer_terms" value="1" class="mt-1 rounded border-charcoal/20 text-brand focus:ring-brand/30" {{ old('accept_organizer_terms') ? 'checked' : '' }} required>
            <span class="text-sm text-charcoal dark:text-[#FAFAFA] leading-relaxed">
                {!! __('Organizer terms acceptance label html', ['url' => route('legal.organizer-terms')]) !!}
            </span>
        </label>
        <x-input-error :messages="$errors->get('accept_organizer_terms')" class="mt-2" />

        <x-primary-button class="w-full py-3">
            {{ __('Finish organizer account') }} <x-icon name="arrow-right" class="w-4 h-4" />
        </x-primary-button>
    </form>
</x-guest-layout>
