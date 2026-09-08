<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-charcoal dark:text-[#FAFAFA]">
            {{ __('Organizer terms accept title') }}
        </h1>
        <p class="mt-2 text-sm text-frost">{{ __('Organizer terms accept subtitle') }}</p>
    </div>

    <div class="max-h-64 overflow-y-auto rounded-xl border border-charcoal/10 dark:border-white/10 bg-cream/50 dark:bg-[#141414] p-4 mb-6 text-sm">
        @include('legal._organizer-terms-content')
    </div>

    <form method="POST" action="{{ route('organizer.terms.store') }}" class="space-y-5">
        @csrf

        <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox"
                   name="accept_organizer_terms"
                   value="1"
                   class="mt-1 rounded border-charcoal/20 text-brand focus:ring-brand/30"
                   {{ old('accept_organizer_terms') ? 'checked' : '' }}
                   required>
            <span class="text-sm text-charcoal dark:text-[#FAFAFA] leading-relaxed">
                {!! __('Organizer terms acceptance label html', ['url' => route('legal.organizer-terms')]) !!}
            </span>
        </label>
        <x-input-error :messages="$errors->get('accept_organizer_terms')" class="mt-2" />

        <x-primary-button class="w-full py-3">
            {{ __('Organizer terms accept button') }}
        </x-primary-button>
    </form>
</x-guest-layout>
