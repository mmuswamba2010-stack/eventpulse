<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center gap-2 font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA] tracking-tight">
            <x-icon name="user-circle" class="w-7 h-7 text-brand" />
            {{ __('My profile') }}
        </h2>
        <p class="mt-1 text-sm text-frost">{{ __('Manage your personal information and account security.') }}</p>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-6 sm:p-8 ep-card shadow-sm">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 ep-card shadow-sm">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 ep-card shadow-sm border border-rose-200 dark:border-rose-900/50">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
