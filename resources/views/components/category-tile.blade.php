@props([
    'category',
    'label',
    'count' => 0,
    'active' => false,
])

@php
    $icon = match ($category) {
        'music' => 'star',
        'tech' => 'chart-bar',
        'art' => 'photo',
        'sport' => 'bolt',
        'conference' => 'building-storefront',
        default => 'squares-2x2',
    };
@endphp

<a {{ $attributes->merge([
    'class' => 'group flex flex-col items-center gap-3 rounded-2xl border bg-white p-5 sm:p-6 text-center no-underline transition hover:border-coral/30 hover:shadow-lift '.($active ? 'border-coral ring-2 ring-coral/20' : 'border-charcoal/[0.08]'),
]) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-coral-muted text-coral transition group-hover:bg-coral group-hover:text-white">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <span class="text-sm font-semibold text-charcoal">{{ $label }}</span>
    <span class="text-xs text-frost">{{ $count }} événement{{ $count > 1 ? 's' : '' }}</span>
</a>
