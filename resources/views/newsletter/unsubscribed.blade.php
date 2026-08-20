<x-guest-layout>
    <div class="mb-8 text-center">
        <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-300 mb-5">
            <x-icon name="check-circle" class="w-8 h-8" />
        </span>
        <h1 class="font-display text-xl font-bold text-charcoal dark:text-[#FAFAFA]">
            {{ ($already ?? false) || session('unsubscribed') ? 'Désinscription confirmée' : 'Déjà désinscrit' }}
        </h1>
        <p class="mt-3 text-sm text-frost">Vous ne recevrez plus la newsletter Event Pulse.</p>
    </div>

    <a href="{{ route('events.index') }}" class="ep-btn w-full py-3 text-center justify-center">
        Retour à l'accueil
    </a>
</x-guest-layout>
