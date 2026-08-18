<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center gap-2 font-extrabold text-2xl text-slate-900 tracking-tight">
            <x-icon name="ticket" class="w-7 h-7 text-brand" />
            Mes Billets
        </h2>
        <p class="mt-1 text-sm text-slate-500">Retrouvez tous vos billets et leurs QR Codes en un coup d'œil.</p>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-4">
            @if ($tickets->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-14 text-center">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mb-4">
                        <x-icon name="ticket" class="w-7 h-7" />
                    </span>
                    <p class="text-slate-500 font-medium">Vous n'avez pas encore de billets.</p>
                    <a href="{{ route('events.index') }}"
                       class="inline-flex items-center gap-1.5 mt-4 px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-700 text-white text-sm font-semibold  hover:brightness-110 transition">
                        Découvrir les événements <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($tickets as $ticket)
                        @php
                            $accent = match ($ticket->status) {
                                'valid' => 'bg-brand',
                                'used' => 'from-slate-300 to-slate-400',
                                default => 'from-rose-400 to-rose-500',
                            };
                        @endphp
                        <div class="relative flex flex-col sm:flex-row bg-white rounded-3xl border border-slate-200/70 shadow-sm hover:shadow-lg transition overflow-hidden">
                            <div class="w-full sm:w-2 h-2 sm:h-auto bg-gradient-to-r sm:bg-gradient-to-b {{ $accent }}"></div>

                            <div class="flex-1 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-slate-900 truncate">{{ $ticket->event->title }}</h3>
                                    <p class="flex items-center flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500 mt-1.5">
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="calendar" class="w-4 h-4 shrink-0 text-slate-400" /> {{ $ticket->event->event_date->translatedFormat('d/m/Y à H:i') }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="map-pin" class="w-4 h-4 shrink-0 text-slate-400" /> {{ $ticket->event->location }}
                                        </span>
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1.5 font-mono tracking-wider">N° {{ $ticket->formatted_number }}</p>
                                    <p class="text-xs font-semibold text-brand mt-1">{{ $ticket->accessLabel() }}</p>
                                </div>

                                <div class="flex items-center gap-2.5 shrink-0">
                                    @if ($ticket->status === 'valid')
                                        <span class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold">
                                            <x-icon name="check-circle" class="w-3.5 h-3.5" /> Valide
                                        </span>
                                    @elseif ($ticket->status === 'used')
                                        <span class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-full bg-slate-100 text-slate-500 font-semibold">
                                            Utilisé
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-full bg-rose-100 text-rose-700 font-semibold">
                                            Annulé
                                        </span>
                                    @endif

                                    <a href="{{ route('tickets.show', $ticket) }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r bg-brand hover:bg-brand-700 text-white text-xs font-bold rounded-full  hover:brightness-110 transition">
                                        <x-icon name="ticket" class="w-3.5 h-3.5" /> QR Code
                                    </a>
                                    <a href="{{ route('tickets.download', $ticket) }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-full transition">
                                        <x-icon name="arrow-down-tray" class="w-3.5 h-3.5" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
