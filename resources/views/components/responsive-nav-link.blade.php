@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-4 pe-4 py-2.5 rounded-lg text-start text-base font-semibold text-coral bg-coral-muted dark:bg-coral/20'
            : 'block w-full ps-4 pe-4 py-2.5 rounded-lg text-start text-base font-medium text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] hover:bg-charcoal/[0.04] dark:hover:bg-white/10 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
