<x-guest-layout>
    <div class="mb-7">
        <p class="ep-kicker mb-2">Récupération</p>
        <h1 class="font-display text-2xl font-bold text-ink">Mot de passe oublié</h1>
        <p class="mt-1.5 text-sm text-ink-muted">Nous vous enverrons un lien de réinitialisation par e-mail.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="envelope" class="w-4.5 h-4.5" />
                </span>
                <x-text-input id="email" class="block w-full pl-10" type="email" name="email" :value="old('email')" required autofocus placeholder="vous@exemple.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            Envoyer le lien <x-icon name="arrow-right" class="w-4 h-4" />
        </x-primary-button>
    </form>
</x-guest-layout>
