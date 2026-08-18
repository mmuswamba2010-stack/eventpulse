<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="flex items-center gap-2 font-extrabold text-2xl text-slate-900 tracking-tight">
                    <x-icon name="calendar" class="w-7 h-7 text-brand" />
                    Mes événements
                </h2>
                <p class="mt-1 text-sm text-slate-500">Créez, modifiez et suivez tous vos événements.</p>
            </div>
            <a href="{{ route('organizer.events.create') }}"
               class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r bg-brand hover:bg-brand-700 text-white text-sm font-semibold rounded-full  hover:brightness-110 transition self-start">
                <x-icon name="plus" class="w-4 h-4" /> Nouvel événement
            </a>
        </div>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            @if ($events->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-14 text-center">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mb-4">
                        <x-icon name="calendar" class="w-7 h-7" />
                    </span>
                    <p class="text-slate-500 font-medium">Vous n'avez pas encore créé d'événement.</p>
                    <a href="{{ route('organizer.events.create') }}"
                       class="inline-flex items-center gap-1.5 mt-4 px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-700 text-white text-sm font-semibold  hover:brightness-110 transition">
                        Créer le premier <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($events as $event)
                        @php $pct = $event->capacity > 0 ? min(100, round(($event->sold_count / $event->capacity) * 100)) : 0; @endphp
                        <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm hover:shadow-lg transition overflow-hidden flex flex-col">
                            <div class="relative h-36 bg-gradient-to-br bg-brand overflow-hidden">
                                @if ($event->image_path)
                                    <img src="{{ asset('storage/'.$event->image_path) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <x-icon name="photo" class="w-10 h-10 text-white/50" />
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/50 to-transparent"></div>
                                <span class="absolute top-3 right-3 text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur
                                    {{ $event->status === 'published' ? 'bg-emerald-500/90 text-white' : ($event->status === 'draft' ? 'bg-amber-500/90 text-white' : 'bg-rose-500/90 text-white') }}">
                                    {{ $event->needsPayment() ? 'En attente de paiement' : ['published' => 'Publié', 'draft' => 'Brouillon', 'cancelled' => 'Annulé'][$event->status] }}
                                </span>
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                @if ($event->isPublished())
                                    <a href="{{ route('events.show', $event->slug) }}" class="font-bold text-slate-900 hover:text-brand transition line-clamp-1">
                                        {{ $event->title }}
                                    </a>
                                @else
                                    <a href="{{ route('organizer.events.edit', $event) }}" class="font-bold text-slate-900 hover:text-brand transition line-clamp-1">
                                        {{ $event->title }}
                                    </a>
                                @endif
                                <p class="flex items-center gap-1.5 text-sm text-slate-500 mt-2">
                                    <x-icon name="map-pin" class="w-4 h-4 shrink-0 text-slate-400" /> <span class="truncate">{{ $event->location }}</span>
                                </p>
                                <p class="flex items-center gap-1.5 text-sm text-slate-500 mt-1">
                                    <x-icon name="calendar" class="w-4 h-4 shrink-0 text-slate-400" /> {{ $event->event_date->format('d/m/Y H:i') }}
                                </p>

                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                                        <span>{{ $event->sold_count }} / {{ $event->capacity }} vendus</span>
                                        <span class="font-semibold text-slate-700">
                                            <x-money :amount="$event->price" />
                                        </span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-xl bg-brand" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>

                                @if ($event->needsPayment())
                                    <a href="{{ route('organizer.events.pay', $event) }}"
                                       class="mt-4 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-gold hover:bg-gold-700 text-ink text-xs font-bold rounded-xl transition">
                                        <x-icon name="banknotes" class="w-3.5 h-3.5" /> Payer &amp; publier
                                    </a>
                                @elseif ($event->is_paid && \App\Models\Event::requiresPublicationPayment())
                                    <p class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600">
                                        <x-icon name="check-badge" class="w-3.5 h-3.5 shrink-0" />
                                        Frais <x-money :amount="$event->publication_fee ?? \App\Models\Event::publicationFee()" /> payés
                                        @if ($event->payment_method)
                                            · {{ $event->payment_method === 'card' ? 'Carte bancaire' : 'Mobile Money' }}
                                        @endif
                                        @if ($event->paid_at)
                                            · {{ $event->paid_at->format('d/m/Y') }}
                                        @endif
                                    </p>
                                @endif

                                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center gap-2">
                                    <a href="{{ route('organizer.events.edit', $event) }}"
                                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-full transition">
                                        <x-icon name="pencil-square" class="w-3.5 h-3.5" /> Modifier
                                    </a>
                                    <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" class="flex-1"
                                          onsubmit="return confirm('Supprimer cet événement et tous ses billets ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-full transition">
                                            <x-icon name="x-circle" class="w-3.5 h-3.5" /> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
