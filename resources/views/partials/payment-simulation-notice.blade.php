@if (config('eventpulse.payment_simulation', true))
    <div class="rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-xs text-amber-900 dark:text-amber-200">
        <p class="font-semibold">Mode démonstration — paiement simulé</p>
        <p class="mt-0.5 text-amber-800/90 dark:text-amber-200/80">Aucun prélèvement réel n'est effectué. Les moyens de paiement servent à indiquer votre choix à l'organisateur.</p>
    </div>
@endif
