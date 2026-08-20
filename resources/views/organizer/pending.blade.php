<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA] tracking-tight">
            {{ auth()->user()->organizer_status === 'rejected' ? __('Organizer rejected title') : __('Organizer pending title') }}
        </h2>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8 px-4">
            <div class="ep-card p-8 text-center">
                <p class="text-frost leading-relaxed">
                    {{ auth()->user()->organizer_status === 'rejected' ? __('Organizer rejected body') : __('Organizer pending body') }}
                </p>
                <form method="POST" action="{{ route('logout') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-brand hover:underline">{{ __('Log out') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
