<section>
    <header class="flex items-center gap-3">
        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-100 text-brand shrink-0">
            <x-icon name="lock-closed" class="w-5 h-5" />
        </span>
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Mot de passe
            </h2>
            <p class="text-sm text-slate-500">
                Utilisez un mot de passe long et unique pour rester en sécurité.
            </p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Mot de passe actuel" />
            <x-password-input id="update_password_current_password" name="current_password" class="mt-1.5 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nouveau mot de passe" />
            <x-password-input id="update_password_password" name="password" class="mt-1.5 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmer le mot de passe" />
            <x-password-input id="update_password_password_confirmation" name="password_confirmation" class="mt-1.5 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Enregistrer</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-600"
                >Enregistré.</p>
            @endif
        </div>
    </form>
</section>
