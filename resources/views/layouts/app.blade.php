<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Event Pulse — billetterie en ligne, QR Code et gestion d'événements.">

        <title>{{ config('app.name', 'Event Pulse') }}</title>

        <link rel="icon" href="{{ asset('images/brand/mark.svg') }}" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <x-flash-messages />

            @isset($header)
                <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                    {{ $header }}
                </div>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer id="about" class="mt-auto bg-charcoal text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
                        <div class="lg:col-span-1">
                            <x-brand-logo tone="light" variant="full" class="shrink-0" />
                            <p class="mt-4 text-sm text-white/60 leading-relaxed max-w-xs">
                                Billetterie en ligne, QR code et scan à l'entrée — réservez vos événements en toute simplicité.
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-white mb-4">Navigation</p>
                            <ul class="space-y-2.5 text-sm text-white/60">
                                <li><a href="{{ route('events.index') }}" class="hover:text-white no-underline transition">Accueil</a></li>
                                <li><a href="{{ route('events.index') }}#events" class="hover:text-white no-underline transition">Événements</a></li>
                                <li><a href="{{ route('events.index') }}#categories" class="hover:text-white no-underline transition">Catégories</a></li>
                                @auth
                                    <li><a href="{{ route('tickets.index') }}" class="hover:text-white no-underline transition">Mes billets</a></li>
                                @else
                                    <li><a href="{{ route('login') }}" class="hover:text-white no-underline transition">Connexion</a></li>
                                @endauth
                            </ul>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-white mb-4">Informations</p>
                            <ul class="space-y-2.5 text-sm text-white/60">
                                <li><a href="{{ route('register') }}" class="hover:text-white no-underline transition">Devenir organisateur</a></li>
                                <li><a href="{{ route('events.index') }}#about" class="hover:text-white no-underline transition">À propos</a></li>
                                <li><span class="text-white/40">Support 24/7</span></li>
                            </ul>
                        </div>

                        <div id="newsletter">
                            <p class="text-sm font-semibold text-white mb-4">Restez informé</p>
                            <p class="text-sm text-white/60 mb-3">Recevez les prochains événements directement dans votre boîte mail.</p>
                            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="space-y-2">
                                @csrf
                                <div class="flex gap-2">
                                    <label for="footer-email" class="sr-only">E-mail</label>
                                    <input id="footer-email" type="email" name="email" required
                                           value="{{ old('email') }}"
                                           placeholder="votre@email.com"
                                           class="flex-1 min-w-0 rounded-lg border-0 bg-white/10 px-3 py-2.5 text-sm text-white placeholder:text-white/40 focus:ring-2 focus:ring-coral/50">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-coral px-3.5 py-2.5 text-white hover:bg-coral-soft transition" aria-label="S'inscrire">
                                        <x-icon name="arrow-right" class="w-4 h-4" />
                                    </button>
                                </div>
                                @if (session('newsletter_success'))
                                    <p class="text-xs text-emerald-300">{{ session('newsletter_success') }}</p>
                                @endif
                                @if (session('newsletter_info'))
                                    <p class="text-xs text-white/70">{{ session('newsletter_info') }}</p>
                                @endif
                                @error('email')
                                    <p class="text-xs text-coral-soft">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    </div>

                    <div class="mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/40">
                        <p>&copy; {{ now()->year }} Event Pulse. Tous droits réservés.</p>
                        <p>Conçu avec soin pour l'événementiel.</p>
                    </div>
                </div>
            </footer>
        </div>
        @stack('scripts')
    </body>
</html>
