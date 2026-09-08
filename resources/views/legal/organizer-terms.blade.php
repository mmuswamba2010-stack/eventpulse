<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <a href="{{ route('events.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] no-underline mb-8">
            <x-icon name="arrow-left" class="w-4 h-4" />
            {{ __('Back to home') }}
        </a>

        <h1 class="font-display text-3xl sm:text-4xl font-bold text-charcoal dark:text-[#FAFAFA]">
            {{ __('Organizer terms title') }}
        </h1>
        <p class="mt-3 text-frost">{{ __('Organizer terms subtitle') }}</p>

        <div class="mt-10 ep-card p-6 sm:p-8">
            @include('legal._organizer-terms-content')
        </div>
    </div>
</x-app-layout>
