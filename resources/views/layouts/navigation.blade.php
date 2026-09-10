<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 dark:bg-[#1A1A1A]/95 backdrop-blur-md border-b border-charcoal/[0.06] dark:border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6 lg:gap-10">
                <x-brand-logo tone="dark" variant="full" class="shrink-0" />

                <div class="hidden lg:flex items-center gap-0.5">
                    <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.index') && ! request('category') && ! request('when') && ! request('search')">
                        {{ __('Home') }}
                    </x-nav-link>
                    <x-nav-link :href="route('events.index').'#events'" :active="false">
                        {{ __('Events') }}
                    </x-nav-link>
                    <x-nav-link :href="route('events.index').'#categories'" :active="false">
                        {{ __('Categories') }}
                    </x-nav-link>

                    @auth
                        <x-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                            {{ __('My tickets') }}
                        </x-nav-link>
                        @if (Auth::user()->isOrganizer())
                            <x-nav-link :href="route('organizer.dashboard')" :active="request()->routeIs('organizer.*')">
                                {{ __('Organizer') }}
                            </x-nav-link>
                        @endif
                        @if (Auth::user()->isAdmin())
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                Administration
                            </x-nav-link>
                        @endif
                    @endauth

                    <x-nav-link :href="route('events.index').'#about'" :active="false">
                        {{ __('About') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden lg:flex lg:items-center lg:gap-2">
                <x-locale-theme-toggle />

                <a href="{{ route('events.index') }}#hero-search" class="inline-flex items-center justify-center p-2 rounded-lg text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] hover:bg-charcoal/[0.04] dark:hover:bg-white/10 transition" aria-label="{{ __('Search') }}">
                    <x-icon name="magnifying-glass" class="w-5 h-5" />
                </a>

                @auth
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 rounded-lg border border-charcoal/10 dark:border-white/15 bg-white dark:bg-[#252525] hover:border-violet/30 transition">
                                <span class="flex items-center justify-center w-7 h-7 rounded-md bg-charcoal text-white text-xs font-bold uppercase">
                                    {{ Str::of(Auth::user()->name)->substr(0, 1) }}
                                </span>
                                <span class="text-sm font-medium text-charcoal dark:text-[#FAFAFA] max-w-[9rem] truncate">{{ Auth::user()->name }}</span>
                                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-frost" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-charcoal/5">
                                <p class="text-sm font-semibold text-charcoal truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-frost truncate">{{ Auth::user()->email }}</p>
                                <span class="inline-flex mt-1.5 px-2 py-0.5 rounded text-[11px] font-semibold {{ Auth::user()->isAdmin() ? 'bg-charcoal text-white' : (Auth::user()->isOrganizer() ? 'bg-violet-muted text-violet' : 'bg-coral-muted text-coral') }}">
                                    @if (Auth::user()->isAdmin())
                                        Admin
                                    @elseif (Auth::user()->isOrganizer())
                                        {{ __('Organizer') }}
                                    @else
                                        {{ __('Participant') }}
                                    @endif
                                </span>
                            </div>

                            @if (Auth::user()->isAdmin())
                                <x-dropdown-link :href="route('admin.dashboard')">Administration</x-dropdown-link>
                            @endif

                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="ep-btn-outline text-sm py-2 px-4">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="ep-btn text-sm py-2 px-4">{{ __('Sign up') }}</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-charcoal dark:text-[#FAFAFA] hover:bg-charcoal/5 dark:hover:bg-white/10 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden border-t border-charcoal/5 dark:border-white/10 bg-white dark:bg-[#1A1A1A]">
        <div class="pt-3 pb-3 space-y-0.5 px-2">
            <x-responsive-nav-link :href="route('events.index')">{{ __('Home') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index').'#events'">{{ __('Events') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index').'#categories'">{{ __('Categories') }}</x-responsive-nav-link>

            @auth
                <x-responsive-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">{{ __('My tickets') }}</x-responsive-nav-link>
                @if (Auth::user()->isOrganizer())
                    <x-responsive-nav-link :href="route('organizer.dashboard')">{{ __('Organizer') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('organizer.events.index')">{{ __('My events') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('organizer.scan.index')">{{ __('Scanner') }}</x-responsive-nav-link>
                @endif
                @if (Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.dashboard')">Administration</x-responsive-nav-link>
                @endif
            @endauth

            <x-responsive-nav-link :href="route('events.index').'#about'">{{ __('About') }}</x-responsive-nav-link>
            <div class="px-4 py-3 flex items-center gap-2">
                <x-locale-theme-toggle />
            </div>
        </div>

        @auth
            <div class="pt-4 pb-4 border-t border-charcoal/5 dark:border-white/10 px-4">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-md bg-charcoal text-white text-sm font-bold uppercase">
                        {{ Str::of(Auth::user()->name)->substr(0, 1) }}
                    </span>
                    <div>
                        <div class="font-medium text-sm text-charcoal dark:text-[#FAFAFA]">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-frost">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-0.5">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log out') }}</x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-4 border-t border-charcoal/5 dark:border-white/10 flex gap-2 px-4">
                <a href="{{ route('login') }}" class="flex-1 ep-btn-outline text-center justify-center">{{ __('Log in') }}</a>
                <a href="{{ route('register') }}" class="flex-1 ep-btn text-center justify-center">{{ __('Sign up') }}</a>
            </div>
        @endauth
    </div>
</nav>
