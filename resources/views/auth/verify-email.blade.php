<x-guest-layout>
    <div class="mb-7">
        <p class="ep-kicker mb-2">Vérification</p>
        <h1 class="font-display text-xl font-bold text-ink">Confirmez votre e-mail</h1>
        <p class="mt-2 text-sm text-ink-muted leading-relaxed">
            Cliquez sur le lien reçu par e-mail pour activer votre compte. Pas reçu ? Demandez un nouvel envoi ci-dessous.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 flex items-center gap-2 font-medium text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
            <x-icon name="check-circle" class="w-4 h-4 shrink-0" />
            Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full py-3">
                Renvoyer l'e-mail de vérification
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-center text-sm font-semibold text-slate-500 hover:text-slate-700 py-2">
                Déconnexion
            </button>
        </form>
    </div>
</x-guest-layout>
