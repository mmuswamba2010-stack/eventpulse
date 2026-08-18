@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-coral bg-coral-muted'
            : 'inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-frost hover:text-charcoal hover:bg-charcoal/[0.04] transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
