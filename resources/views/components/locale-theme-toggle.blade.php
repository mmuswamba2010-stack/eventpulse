@php
    $currentLocale = app()->getLocale();
@endphp

<div class="inline-flex items-center gap-1">
    <div class="inline-flex items-center gap-1 rounded-lg border border-charcoal/10 dark:border-white/10 bg-white dark:bg-[#1A1A1A] p-0.5">
    <a href="{{ route('locale.switch', 'fr') }}"
       class="px-2 py-1 text-xs font-semibold rounded-md transition {{ $currentLocale === 'fr' ? 'bg-charcoal text-white dark:bg-cream dark:text-charcoal' : 'text-frost hover:text-charcoal dark:hover:text-cream' }}"
       aria-current="{{ $currentLocale === 'fr' ? 'true' : 'false' }}">
        FR
    </a>
    <a href="{{ route('locale.switch', 'en') }}"
       class="px-2 py-1 text-xs font-semibold rounded-md transition {{ $currentLocale === 'en' ? 'bg-charcoal text-white dark:bg-cream dark:text-charcoal' : 'text-frost hover:text-charcoal dark:hover:text-cream' }}"
       aria-current="{{ $currentLocale === 'en' ? 'true' : 'false' }}">
        EN
    </a>
</div>

<button
    type="button"
    data-theme-toggle
    class="inline-flex items-center justify-center p-2 rounded-lg text-frost hover:text-charcoal dark:hover:text-cream hover:bg-charcoal/[0.04] dark:hover:bg-white/10 transition"
    aria-label="{{ __('Theme') }}"
>
    <svg data-theme-icon-light class="w-5 h-5 hidden dark:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
    </svg>
    <svg data-theme-icon-dark class="w-5 h-5 block dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
    </svg>
</button>
</div>
