<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-charcoal/[0.06]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6 lg:gap-10">
                <x-brand-logo tone="dark" variant="full" class="shrink-0" />

                <div class="hidden lg:flex items-center gap-0.5">
                    <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.index') && ! request('category') && ! request('when') && ! request('search')">
                        Accueil
                    </x-nav-link>
                    <x-nav-link :href="route('events.index').'#events'" :active="false">
                        Événements
                    </x-nav-link>
                    <x-nav-link :href="route('events.index').'#categories'" :active="false">
                        Catégories
                    </x-nav-link>

                    @auth
                        <x-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                            Mes billets
                        </x-nav-link>
                        @if (Auth::user()->isOrganizer())
                            <x-nav-link :href="route('organizer.dashboard')" :active="request()->routeIs('organizer.*')">
                                Organisateur
                            </x-nav-link>
                        @endif
                    @endauth

                    <x-nav-link :href="route('events.index').'#about'" :active="false">
                        À propos
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden lg:flex lg:items-center lg:gap-2">
                <a href="{{ route('events.index') }}#hero-search" class="inline-flex items-center justify-center p-2 rounded-lg text-frost hover:text-charcoal hover:bg-charcoal/[0.04] transition" aria-label="Rechercher">
                    <x-icon name="magnifying-glass" class="w-5 h-5" />
                </a>

                @auth
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 rounded-lg border border-charcoal/10 bg-white hover:border-violet/30 transition">
                                <span class="flex items-center justify-center w-7 h-7 rounded-md bg-charcoal text-white text-xs font-bold uppercase">
                                    {{ Str::of(Auth::user()->name)->substr(0, 1) }}
                                </span>
                                <span class="text-sm font-medium text-charcoal max-w-[9rem] truncate">{{ Auth::user()->name }}</span>
                                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-frost" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-charcoal/5">
                                <p class="text-sm font-semibold text-charcoal truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-frost truncate">{{ Auth::user()->email }}</p>
                                <span class="inline-flex mt-1.5 px-2 py-0.5 rounded text-[11px] font-semibold {{ Auth::user()->isOrganizer() ? 'bg-violet-muted text-violet' : 'bg-coral-muted text-coral' }}">
                                    {{ Auth::user()->isOrganizer() ? 'Organisateur' : 'Participant' }}
                                </span>
                            </div>

                            <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    Déconnexion
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="ep-btn-outline text-sm py-2 px-4">Se connecter</a>
                    <a href="{{ route('register') }}" class="ep-btn text-sm py-2 px-4">S'inscrire</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-charcoal hover:bg-charcoal/5 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden border-t border-charcoal/5 bg-white">
        <div class="pt-3 pb-3 space-y-0.5 px-2">
            <x-responsive-nav-link :href="route('events.index')">Accueil</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index').'#events'">Événements</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index').'#categories'">Catégories</x-responsive-nav-link>

            @auth
                <x-responsive-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">Mes billets</x-responsive-nav-link>
                @if (Auth::user()->isOrganizer())
                    <x-responsive-nav-link :href="route('organizer.dashboard')">Organisateur</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('organizer.events.index')">Mes événements</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('organizer.scan.index')">Scanner</x-responsive-nav-link>
                @endif
            @endauth

            <x-responsive-nav-link :href="route('events.index').'#about'">À propos</x-responsive-nav-link>
        </div>

        @auth
            <div class="pt-4 pb-4 border-t border-charcoal/5 px-4">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-md bg-charcoal text-white text-sm font-bold uppercase">
                        {{ Str::of(Auth::user()->name)->substr(0, 1) }}
                    </span>
                    <div>
                        <div class="font-medium text-sm text-charcoal">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-frost">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-0.5">
                    <x-responsive-nav-link :href="route('profile.edit')">Profil</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Déconnexion</x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-4 border-t border-charcoal/5 flex gap-2 px-4">
                <a href="{{ route('login') }}" class="flex-1 ep-btn-outline text-center justify-center">Se connecter</a>
                <a href="{{ route('register') }}" class="flex-1 ep-btn text-center justify-center">S'inscrire</a>
            </div>
        @endauth
    </div>
</nav>
