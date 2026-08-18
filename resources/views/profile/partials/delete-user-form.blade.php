<section class="space-y-5">
    <header class="flex items-center gap-3">
        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-rose-100 text-rose-600 shrink-0">
            <x-icon name="exclamation-triangle" class="w-5 h-5" />
        </span>
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Supprimer le compte
            </h2>
            <p class="text-sm text-slate-500">
                Cette action est définitive. Toutes vos données seront supprimées.
            </p>
        </div>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <x-icon name="exclamation-triangle" class="w-4 h-4" /> Supprimer le compte
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-900">
                Confirmer la suppression du compte
            </h2>

            <p class="mt-1.5 text-sm text-slate-500">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Merci de saisir votre mot de passe pour confirmer.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Mot de passe" class="sr-only" />

                <x-password-input
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    placeholder="Mot de passe"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Annuler
                </x-secondary-button>

                <x-danger-button>
                    Supprimer le compte
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
