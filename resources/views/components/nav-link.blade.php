@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-coral bg-coral-muted dark:bg-coral/20'
            : 'inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] hover:bg-charcoal/[0.04] dark:hover:bg-white/10 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
