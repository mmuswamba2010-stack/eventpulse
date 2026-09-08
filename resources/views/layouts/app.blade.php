<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        @include('partials.theme-init')
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @include('partials.seo-meta', [
            'seoTitle' => $seoTitle ?? null,
            'seoDescription' => $seoDescription ?? null,
            'seoUrl' => $seoUrl ?? null,
            'seoImage' => $seoImage ?? null,
            'seoType' => $seoType ?? null,
        ])

        <link rel="icon" href="{{ asset('images/brand/mark.svg') }}" type="image/svg+xml">
        @include('partials.web-fonts')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-cream dark:bg-[#0F0F0F] text-charcoal dark:text-[#FAFAFA]">
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
                                {{ __('Online ticketing, QR code and entry scanning — book your events with ease.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-white mb-4">{{ __('Navigation') }}</p>
                            <ul class="space-y-2.5 text-sm text-white/60">
                                <li><a href="{{ route('events.index') }}" class="hover:text-white no-underline transition">{{ __('Home') }}</a></li>
                                <li><a href="{{ route('events.index') }}#events" class="hover:text-white no-underline transition">{{ __('Events') }}</a></li>
                                <li><a href="{{ route('events.index') }}#categories" class="hover:text-white no-underline transition">{{ __('Categories') }}</a></li>
                                @auth
                                    <li><a href="{{ route('tickets.index') }}" class="hover:text-white no-underline transition">{{ __('My tickets') }}</a></li>
                                @else
                                    <li><a href="{{ route('login') }}" class="hover:text-white no-underline transition">{{ __('Log in') }}</a></li>
                                @endauth
                            </ul>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-white mb-4">{{ __('Information') }}</p>
                            <ul class="space-y-2.5 text-sm text-white/60">
                                <li><a href="{{ route('register.organizer') }}" class="hover:text-white no-underline transition">{{ __('Become an organizer') }}</a></li>
                                <li><a href="{{ route('legal.organizer-terms') }}" class="hover:text-white no-underline transition">{{ __('Organizer terms title') }}</a></li>
                                <li><a href="{{ route('events.index') }}#about" class="hover:text-white no-underline transition">{{ __('About') }}</a></li>
                                <li><span class="text-white/40">Support 24/7</span></li>
                            </ul>
                        </div>

                        <div id="newsletter">
                            <p class="text-sm font-semibold text-white mb-4">{{ __('Stay informed') }}</p>
                            <p class="text-sm text-white/60 mb-3">{{ __('Get upcoming events delivered straight to your inbox.') }}</p>
                            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="space-y-2">
                                @csrf
                                <div class="flex gap-2">
                                    <label for="footer-email" class="sr-only">{{ __('Email address') }}</label>
                                    <input id="footer-email" type="email" name="email" required
                                           value="{{ old('email') }}"
                                           placeholder="{{ __('your@email.com') }}"
                                           class="flex-1 min-w-0 rounded-lg border-0 bg-white/10 px-3 py-2.5 text-sm text-white placeholder:text-white/40 focus:ring-2 focus:ring-coral/50">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-coral px-3.5 py-2.5 text-white hover:bg-coral-soft transition" aria-label="{{ __('Subscribe') }}">
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
                        <p>&copy; {{ now()->year }} Event Pulse. {{ __('All rights reserved.') }}</p>
                        <p>{{ __('Crafted with care for the events industry.') }}</p>
                    </div>
                </div>
            </footer>
        </div>
        @include('partials.password-toggle')
        @stack('scripts')
    </body>
</html>
