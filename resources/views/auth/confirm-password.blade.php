<x-guest-layout>
    <div class="mb-7">
        <p class="ep-kicker mb-2">Confirmation</p>
        <h1 class="font-display text-xl font-bold text-ink">Confirmez votre identité</h1>
        <p class="mt-1.5 text-sm text-ink-muted">Saisissez votre mot de passe pour continuer.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-password-input id="password" class="block mt-1.5 w-full"
                            name="password"
                            required autocomplete="current-password" autofocus />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            Confirmer
        </x-primary-button>
    </form>
</x-guest-layout>
