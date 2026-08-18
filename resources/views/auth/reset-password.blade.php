<x-guest-layout>
    <div class="mb-7">
        <p class="ep-kicker mb-2">Sécurité</p>
        <h1 class="font-display text-2xl font-bold text-ink">Nouveau mot de passe</h1>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Nouveau mot de passe" />
            <x-password-input id="password" class="block mt-1.5 w-full" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-password-input id="password_confirmation" class="block mt-1.5 w-full"
                                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            Réinitialiser le mot de passe
        </x-primary-button>
    </form>
</x-guest-layout>
