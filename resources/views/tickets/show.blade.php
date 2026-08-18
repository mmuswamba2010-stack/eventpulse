<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700">
            <x-icon name="arrow-left" class="w-4 h-4" /> Mes billets
        </a>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-md mx-auto px-4">
            <div class="relative bg-white rounded-3xl shadow-2xl shadow-slate-300/40 overflow-hidden">
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

                <!-- Séparateur perforé façon billet -->
                <div class="relative">
                    <div class="absolute -left-3 -top-3 w-6 h-6 rounded-full bg-slate-50"></div>
                    <div class="absolute -right-3 -top-3 w-6 h-6 rounded-full bg-slate-50"></div>
                    <div class="border-t-2 border-dashed border-slate-200"></div>
                </div>

                <div class="p-7 text-center">
                    <div class="mb-4 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-brand-50 border border-brand-100 text-brand-800 text-sm font-bold tracking-wide">
                        {{ $ticket->accessLabel() }}
                    </div>
                    <div class="inline-block bg-white p-3 rounded-2xl border border-slate-200 shadow-sm">
                        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code du billet" class="w-56 h-56">
                    </div>
                    <p class="mt-3 text-sm font-mono font-bold tracking-widest text-slate-800">{{ $ticket->formatted_number }}</p>
                    <p class="mt-1 text-[10px] text-slate-400">Réf. billet — présentez le QR à l'entrée</p>
                </div>

                <div class="px-7 pb-2 space-y-2.5 text-sm">
                    <div class="flex items-start justify-between gap-4 py-1.5">
                        <span class="text-slate-400 shrink-0">Titulaire</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $ticket->user->name }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-slate-100">
                        <span class="text-slate-400 shrink-0">Pass</span>
                        <span class="font-medium text-slate-700 text-right">{{ $ticket->ticketType?->name ?? 'Standard' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-slate-100">
                        <span class="text-slate-400 shrink-0">Organisateur</span>
                        <span class="font-medium text-slate-700 text-right">{{ $ticket->event->user->name }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-slate-100">
                        <span class="text-slate-400 shrink-0">Prix</span>
                        <span class="font-medium text-slate-700 text-right">{{ $ticket->displayPrice() }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-slate-100">
                        <span class="text-slate-400 shrink-0">Paiement</span>
                        <span class="font-medium text-slate-700 text-right">{{ $ticket->paymentMethodLabel() }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-1.5 border-t border-slate-100">
                        <span class="text-slate-400 shrink-0">Statut</span>
                        @if ($ticket->status === 'valid')
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                                <x-icon name="check-circle" class="w-4 h-4" /> Valide
                            </span>
                        @elseif ($ticket->status === 'used')
                            <span class="text-slate-500 font-semibold text-right">Utilisé le {{ $ticket->scanned_at?->format('d/m/Y à H:i') }}</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-rose-600 font-semibold">
                                <x-icon name="x-circle" class="w-4 h-4" /> Annulé
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-7 pt-4 flex gap-3">
                    <a href="{{ route('tickets.download', $ticket) }}"
                       class="flex-1 flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-full px-4 py-3 transition">
                        <x-icon name="arrow-down-tray" class="w-4 h-4" /> PDF
                    </a>
                    <a href="{{ route('tickets.index') }}"
                       class="flex-1 flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-full px-4 py-3 transition">
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
