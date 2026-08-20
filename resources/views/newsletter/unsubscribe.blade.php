<x-guest-layout>
    <div class="mb-7 text-center">
        <h1 class="font-display text-xl font-bold text-charcoal dark:text-[#FAFAFA]">Se désinscrire de la newsletter</h1>
        <p class="mt-2 text-sm text-frost">Vous ne recevrez plus les e-mails Event Pulse à <strong>{{ $subscriber->email }}</strong>.</p>
    </div>

    <form method="POST" action="{{ route('newsletter.unsubscribe.confirm', $subscriber->unsubscribe_token) }}">
        @csrf
        <x-primary-button class="w-full py-3">Confirmer la désinscription</x-primary-button>
    </form>
</x-guest-layout>
