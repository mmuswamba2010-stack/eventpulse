@props([
    'variant' => 'full',
    'tone' => 'dark',
])

@php
    $onDark = $tone === 'light';
@endphp

<a {{ $attributes->merge(['href' => route('events.index'), 'class' => 'inline-flex items-center gap-2.5 group shrink-0']) }}>
    <img src="{{ asset('images/brand/mark.svg') }}" alt=""
         class="h-9 w-9 shrink-0 transition group-hover:scale-[1.03]"
         width="36" height="36">
    @if ($variant === 'full')
        <span class="ep-logo-text notranslate" translate="no">
            <span class="{{ $onDark ? 'text-frost' : 'text-frost' }}">Event</span>
            <span class="{{ $onDark ? 'text-white' : 'text-charcoal' }}"> Pulse</span>
        </span>
    @endif
</a>
