<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-frost hover:text-charcoal dark:hover:text-[#FAFAFA]">
            <x-icon name="arrow-left" class="w-4 h-4" /> {{ __('My tickets') }}
        </a>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-md mx-auto px-4">
            <div class="relative ep-card rounded-3xl shadow-2xl overflow-hidden">
                <div class="relative bg-gradient-to-br from-ink via-brand-800 to-brand-700 text-white p-7 text-center overflow-hidden">
                    <p class="relative text-[11px] font-bold uppercase tracking-[0.2em] opacity-80 font-display notranslate" translate="no">Event Pulse</p>
                    <h1 class="relative font-display text-xl font-extrabold mt-2 leading-tight">{{ $ticket->event->title }}</h1>
                    <div class="relative text-sm mt-3 opacity-90 space-y-1.5">
                        <p class="flex items-center justify-center gap-1.5">
                            <x-icon name="calendar" class="w-4 h-4 shrink-0" /> {{ $ticket->event->event_date->translatedFormat('d/m/Y à H:i') }}
                        </p>
                        <p class="flex items-center justify-center gap-1.5">
                            <x-icon name="map-pin" class="w-4 h-4 shrink-0" /> {{ $ticket->event->location }}
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -left-3 -top-3 w-6 h-6 rounded-full bg-cream dark:bg-[#0F0F0F]"></div>
                    <div class="absolute -right-3 -top-3 w-6 h-6 rounded-full bg-cream dark:bg-[#0F0F0F]"></div>
                    <div class="border-t-2 border-dashed border-charcoal/10 dark:border-white/10"></div>
                </div>

                <div class="p-7 text-center">
                    <div class="mb-4 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-brand-50 dark:bg-brand/20 border border-brand-100 dark:border-brand/30 text-brand-800 dark:text-brand text-sm font-bold tracking-wide">
                        {{ $ticket->accessLabel() }}
                    </div>
                    <div class="inline-block bg-white dark:bg-[#141414] p-3 rounded-2xl border border-charcoal/10 dark:border-white/10 shadow-sm">
                        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code du billet" class="w-56 h-56">
                    </div>
                    <p class="mt-3 text-sm font-mono font-bold tracking-widest text-charcoal dark:text-[#FAFAFA]">{{ $ticket->formatted_number }}</p>
                    <p class="mt-1 text-[10px] text-frost">Réf. billet — présentez le QR à l'entrée</p>
                </div>

                <div class="px-7 pb-2 space-y-2.5 text-sm">
                    <div class="flex items-start justify-between gap-4 py-1.5">
                        <span class="text-frost shrink-0">Titulaire</span>
                        <span class="font-semibold text-charcoal dark:text-[#FAFAFA] text-right">{{ $ticket->user->name }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-charcoal/5 dark:border-white/5">
                        <span class="text-frost shrink-0">Pass</span>
                        <span class="font-medium text-charcoal dark:text-[#FAFAFA] text-right">{{ $ticket->ticketType?->name ?? 'Standard' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-charcoal/5 dark:border-white/5">
                        <span class="text-frost shrink-0">Organisateur</span>
                        <span class="font-medium text-charcoal dark:text-[#FAFAFA] text-right">{{ $ticket->event->user->name }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-charcoal/5 dark:border-white/5">
                        <span class="text-frost shrink-0">Prix</span>
                        <span class="font-medium text-charcoal dark:text-[#FAFAFA] text-right">{{ $ticket->displayPrice() }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-charcoal/5 dark:border-white/5">
                        <span class="text-frost shrink-0">Paiement</span>
                        <span class="font-medium text-charcoal dark:text-[#FAFAFA] text-right">{{ $ticket->paymentMethodLabel() }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-charcoal/5 dark:border-white/5">
                        <span class="text-frost shrink-0">Statut</span>
                        @if ($ticket->status === 'valid')
                            <span class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 font-semibold">
                                <x-icon name="check-circle" class="w-4 h-4" /> {{ __('Valid') }}
                            </span>
                        @elseif ($ticket->status === 'used')
                            <span class="text-frost font-semibold text-right">Utilisé le {{ $ticket->scanned_at?->format('d/m/Y à H:i') }}</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-rose-600 dark:text-rose-400 font-semibold">
                                <x-icon name="x-circle" class="w-4 h-4" /> {{ __('Cancelled') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-7 pt-4 flex gap-3">
                    <a href="{{ route('tickets.download', $ticket) }}"
                       class="flex-1 flex items-center justify-center gap-2 bg-charcoal dark:bg-violet hover:opacity-90 text-white text-sm font-semibold rounded-full px-4 py-3 transition">
                        <x-icon name="arrow-down-tray" class="w-4 h-4" /> PDF
                    </a>
                    <a href="{{ route('tickets.index') }}"
                    class="flex-1 flex items-center justify-center gap-2 bg-charcoal/5 dark:bg-white/10 hover:bg-charcoal/10 dark:hover:bg-white/15 text-charcoal dark:text-[#FAFAFA] text-sm font-semibold rounded-full px-4 py-3 transition">
                        {{ __('Back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
