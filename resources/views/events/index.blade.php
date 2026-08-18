<x-app-layout>
    @php
        $filterParams = fn (?string $cat = null, ?string $whenVal = null) => array_filter([
            'search' => $search ?: null,
            'category' => $cat,
            'when' => $whenVal ?? ($when ?: null),
        ]);
        $gridParams = array_filter([
            'search' => $search ?: null,
            'category' => $category ?: null,
            'when' => $when ?: null,
        ]);
        $sectionTitle = match (true) {
            (bool) $search => 'Résultats de recherche',
            (bool) $category => \App\Models\Event::CATEGORIES[$category] ?? 'Événements',
            $when === 'today' => 'Aujourd\'hui',
            $when === 'weekend' => 'Ce week-end',
            default => 'Événements à ne pas manquer',
        };
    @endphp

    <div class="min-h-screen bg-white"
         x-data="eventCatalog({
            gridUrl: @js(route('events.grid', $gridParams)),
            page: @js(max(1, (int) request('page', 1))),
            initialTotal: @js($events->total()),
         })"
         x-init="hydrate()">

        {{-- Hero --}}
        <section id="hero-search" class="relative overflow-hidden bg-white border-b border-charcoal/[0.05]">
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute -top-28 left-1/2 -translate-x-1/2">
                    <div class="h-[min(500px,78vw)] w-[min(900px,115vw)] rounded-full bg-gradient-to-b from-coral-muted/75 via-coral-muted/35 to-transparent blur-3xl"></div>
                </div>
                <div class="absolute top-6 left-1/2 -translate-x-1/2 h-[min(340px,52vw)] w-[min(620px,88vw)] rounded-full bg-coral-muted/25 blur-2xl"></div>
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-b from-transparent to-white" aria-hidden="true"></div>

            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 text-center">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-charcoal leading-tight">
                    Vivez des expériences<br class="hidden sm:inline">
                    <span class="bg-gradient-to-r from-coral to-brand-700 bg-clip-text text-transparent">inoubliables.</span>
                </h1>
                <p class="mt-4 text-base sm:text-lg text-frost max-w-2xl mx-auto leading-relaxed">
                    Découvrez et réservez les meilleures événements autour de vous.
                    <br>
                    Concerts, conférences, festivals, spectacles, et bien plus encore.
                </p>

                <form method="GET" action="{{ route('events.index') }}" class="mt-8 max-w-xl mx-auto w-full">
                    @if ($category)
                        <input type="hidden" name="category" value="{{ $category }}">
                    @endif
                    <label for="catalog-search" class="sr-only">Rechercher</label>
                    <div class="flex items-center gap-2 rounded-full border border-charcoal/[0.1] bg-white shadow-sm pl-5 pr-1.5 py-1.5 focus-within:ring-2 focus-within:ring-coral/20">
                        <x-icon name="magnifying-glass" class="w-5 h-5 text-frost shrink-0" />
                        <input id="catalog-search" type="text" name="search"
                            placeholder="Concert, conférence, lieu…"
                            class="flex-1 min-w-0 bg-transparent border-0 focus:ring-0 text-sm sm:text-base text-charcoal placeholder:text-frost py-2.5 sm:py-3"
                            value="{{ $search ?? '' }}">
                        <button type="submit" class="ep-btn rounded-full px-5 sm:px-7 py-2.5 sm:py-3 text-sm shrink-0">
                            Rechercher
                        </button>
                    </div>
                </form>

                <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-sm text-frost">
                    <li class="inline-flex items-center gap-2">
                        <x-icon name="shield-check" class="w-5 h-5 text-coral shrink-0" />
                        Paiement sécurisé
                    </li>
                    <li class="inline-flex items-center gap-2">
                        <x-icon name="ticket" class="w-5 h-5 text-coral shrink-0" />
                        Billet électronique
                    </li>
                </ul>
            </div>
        </section>

        {{-- Catégories --}}
        <section id="categories" class="bg-cream/50 py-12 sm:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 sm:mb-10">
                    <h2 class="text-xl sm:text-2xl font-bold text-charcoal">Parcourir par catégorie</h2>
                    <p class="mt-2 text-sm text-frost">Trouvez l'événement qui vous correspond</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-5">
                    @foreach (\App\Models\Event::CATEGORIES as $key => $label)
                        @continue($key === 'other')
                        <x-category-tile
                            :category="$key"
                            :label="$label"
                            :count="$categoryCounts[$key] ?? 0"
                            :active="$category === $key"
                            :href="route('events.index', $filterParams($key))"
                        />
                    @endforeach
                    <a href="{{ route('events.index') }}#events"
                       class="group flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-charcoal/15 bg-white p-5 sm:p-6 text-center no-underline transition hover:border-coral/40 hover:bg-coral-muted/30">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-charcoal/[0.04] text-charcoal group-hover:bg-coral group-hover:text-white transition">
                            <x-icon name="plus" class="h-6 w-6" />
                        </span>
                        <span class="text-sm font-semibold text-charcoal">Voir plus</span>
                        <span class="text-xs text-frost">Tout le catalogue</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- Catalogue --}}
        <section id="events" class="bg-white py-12 sm:py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-3 mb-10">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-charcoal">{{ $sectionTitle }}</h2>
                        @if ($search)
                            <p class="mt-1 text-sm text-frost">« {{ $search }} »</p>
                        @elseif (! $category)
                            <p class="mt-1 text-sm text-frost">Les meilleures expériences près de chez vous</p>
                        @endif
                        @if ($search || $category)
                            <a href="{{ route('events.index') }}"
                               class="inline-block mt-2 text-sm font-medium text-coral hover:text-brand-700 no-underline">
                                Effacer les filtres
                            </a>
                        @endif
                    </div>
                    <p class="text-sm text-frost">
                        <span x-text="total" class="font-semibold text-charcoal">{{ $events->total() }}</span>
                        événement(s)
                    </p>
                </div>

                <div x-show="loading" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8" aria-busy="true">
                    @for ($i = 0; $i < 8; $i++)
                        <x-event-card-skeleton />
                    @endfor
                </div>

                <div x-show="!loading" x-ref="grid">
                    @include('events._grid', ['events' => $events, 'search' => $search, 'category' => $category])
                </div>
            </div>
        </section>

        {{-- Avantages --}}
        <section class="border-t border-charcoal/[0.06] bg-cream/40 py-12 sm:py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center sm:text-left">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-coral-muted text-coral mb-3">
                            <x-icon name="bolt" class="w-5 h-5" />
                        </span>
                        <p class="font-semibold text-charcoal">Réservation rapide</p>
                        <p class="mt-1 text-sm text-frost leading-relaxed">Billets confirmés en quelques secondes.</p>
                    </div>
                    <div class="text-center sm:text-left">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-coral-muted text-coral mb-3">
                            <x-icon name="shield-check" class="w-5 h-5" />
                        </span>
                        <p class="font-semibold text-charcoal">Paiement sécurisé</p>
                        <p class="mt-1 text-sm text-frost leading-relaxed">Mobile Money, carte ou espèces.</p>
                    </div>
                    <div class="text-center sm:text-left">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-coral-muted text-coral mb-3">
                            <x-icon name="device-phone-mobile" class="w-5 h-5" />
                        </span>
                        <p class="font-semibold text-charcoal">Billet mobile</p>
                        <p class="mt-1 text-sm text-frost leading-relaxed">QR code sur votre téléphone, prêt à scanner.</p>
                    </div>
                    <div class="text-center sm:text-left">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-coral-muted text-coral mb-3">
                            <x-icon name="phone" class="w-5 h-5" />
                        </span>
                        <p class="font-semibold text-charcoal">Support 24/7</p>
                        <p class="mt-1 text-sm text-frost leading-relaxed">Une équipe disponible pour vous aider.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
