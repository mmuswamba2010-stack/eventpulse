<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center gap-2 font-extrabold text-2xl text-slate-900 tracking-tight">
            <x-icon name="user-circle" class="w-7 h-7 text-brand" />
            Mon profil
        </h2>
        <p class="mt-1 text-sm text-slate-500">Gérez vos informations personnelles et la sécurité de votre compte.</p>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-6 sm:p-8 bg-white shadow-sm border border-slate-200 rounded-3xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white shadow-sm border border-slate-200 rounded-3xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white shadow-sm border border-rose-200 rounded-3xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
