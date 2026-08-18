@props([
    'category',
    'label',
    'active' => false,
    'tone' => 'light',
])

@php
    $onDark = $tone === 'dark';
    $icon = match ($category) {
        'music' => 'star',
        'tech' => 'chart-bar',
        'art' => 'photo',
        'sport' => 'bolt',
        'conference' => 'building-storefront',
        default => 'ticket',
    };
@endphp

<a {{ $attributes->merge([
    'class' => 'group flex flex-col items-center gap-2.5 w-[4.5rem] sm:w-20 shrink-0 no-underline',
]) }}>
    <span @class([
        'flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-full border transition shadow-sm',
        'bg-white border-coral ring-2 ring-coral/30' => $active && $onDark,
        'bg-white/10 border-white/20 group-hover:bg-white/15 group-hover:border-white/30' => ! $active && $onDark,
        'bg-white border-coral ring-2 ring-coral/20' => $active && ! $onDark,
        'bg-white border-charcoal/10 group-hover:border-charcoal/25 group-hover:shadow-md' => ! $active && ! $onDark,
    ])>
        <x-icon :name="$icon" @class([
            'w-6 h-6',
            'text-coral' => $active,
            'text-white/80 group-hover:text-white' => ! $active && $onDark,
            'text-charcoal/70 group-hover:text-charcoal' => ! $active && ! $onDark,
        ]) />
    </span>
    <span @class([
        'text-[11px] sm:text-xs font-medium text-center leading-tight',
        'text-white font-semibold' => $active && $onDark,
        'text-frost group-hover:text-white' => ! $active && $onDark,
        'text-charcoal font-semibold' => $active && ! $onDark,
        'text-frost group-hover:text-charcoal' => ! $active && ! $onDark,
    ])>
        {{ $label }}
    </span>
</a>
