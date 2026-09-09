@props([
    'context' => 'login',
])

@php
    $providers = \App\Support\SocialAuth::configuredProviders();
@endphp

@if ($providers !== [])
    <div {{ $attributes->class(['space-y-3']) }}>
        <div class="relative py-1">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-charcoal/10 dark:border-white/10"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase tracking-wide">
                <span class="bg-white dark:bg-[#1A1A1A] px-3 text-frost">{{ __('Or continue with') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-2.5">
            @foreach ($providers as $provider)
                <a href="{{ route('social.redirect', ['provider' => $provider, 'context' => $context]) }}"
                   class="inline-flex items-center justify-center gap-2.5 w-full rounded-xl border border-charcoal/10 dark:border-white/10 bg-white dark:bg-[#141414] px-4 py-3 text-sm font-semibold text-charcoal dark:text-[#FAFAFA] no-underline transition hover:border-brand/30 hover:bg-brand-50/40 dark:hover:bg-brand-950/20">
                    @if ($provider === 'google')
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#EA4335" d="M12 10.2v3.6h5.1c-.2 1.2-1.6 3.5-5.1 3.5-3.1 0-5.6-2.5-5.6-5.6S8.9 6.1 12 6.1c1.8 0 3 .8 3.7 1.5l2.5-2.4C16.8 3.7 14.6 2.7 12 2.7 6.9 2.7 2.8 6.8 2.8 12s4.1 9.3 9.2 9.3c5.3 0 8.8-3.7 8.8-9 0-.6-.1-1.1-.2-1.6H12z"/>
                            <path fill="#34A853" d="M3.9 7.9 6.8 10c.7-1.4 2-2.4 3.6-2.4 1 0 1.8.3 2.4.9l2.5-2.4C14.1 5.3 13.1 4.9 12 4.9 9.1 4.9 6.7 7.3 6.7 10.2H3.9z"/>
                            <path fill="#4A90E2" d="M3.9 16.1c1.1 2.1 3.3 3.5 5.9 3.5 1.7 0 3.1-.6 4.1-1.5l-1.9-1.5c-.6.4-1.3.6-2.2.6-1.7 0-3.1-1.1-3.6-2.7l-4.3 3.6z"/>
                            <path fill="#FBBC05" d="M12 19.6c2.4 0 4.4-.8 5.9-2.2l-2.8-2.3c-.8.5-1.8.8-3.1.8-2.4 0-4.4-1.6-5.1-3.8l-4.3 3.6c1.5 3 4.6 4.9 8.4 4.9z"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 shrink-0 text-[#1877F2]" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.413c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.234 2.686.234v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                        </svg>
                    @endif
                    {{ \App\Support\SocialAuth::providerLabel($provider) }}
                </a>
            @endforeach
        </div>
    </div>
@endif
