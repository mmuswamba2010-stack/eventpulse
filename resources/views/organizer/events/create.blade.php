<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('organizer.events.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] mb-2">
            <x-icon name="arrow-left" class="w-4 h-4" /> {{ __('My events') }}
        </a>
        <h2 class="flex items-center gap-2 font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA] tracking-tight">
            <x-icon name="plus" class="w-7 h-7 text-brand" />
            {{ __('Create event') }}
        </h2>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 px-4">
            <div class="ep-card shadow-sm p-6 sm:p-8">
                <form method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data">
                    @csrf

                    @include('organizer.events._form')

                    <div class="mt-8 pt-6 border-t border-charcoal/5 dark:border-white/5 flex items-center justify-end gap-3">
                        <a href="{{ route('organizer.events.index') }}"
                           class="px-5 py-2.5 text-sm font-semibold text-frost hover:text-charcoal dark:hover:text-[#FAFAFA]">{{ __('Cancel') }}</a>
                        <x-primary-button class="py-3 px-6">
                            <x-icon name="check" class="w-4 h-4" /> {{ __('Create event button') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
