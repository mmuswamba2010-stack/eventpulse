@props([
    'event' => null,
    'src' => null,
    'alt' => '',
    'variant' => 'card',
    'loading' => null,
    'fetchpriority' => null,
])

@php
    $path = $event?->image_path;
    $resolvedSrc = $src ?? match ($variant) {
        'card', 'thumb' => \App\Support\EventImage::thumbUrl($path),
        default => \App\Support\EventImage::url($path),
    };
    $resolvedLoading = $loading ?? match ($variant) {
        'hero' => 'eager',
        default => 'lazy',
    };
    $resolvedFetchpriority = $fetchpriority ?? ($variant === 'hero' ? 'high' : null);
    $sizes = match ($variant) {
        'card', 'thumb' => '(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw',
        'hero' => '100vw',
        default => null,
    };
@endphp

@if ($resolvedSrc)
    <img src="{{ $resolvedSrc }}"
         alt="{{ $alt }}"
         @if ($sizes) sizes="{{ $sizes }}" @endif
         loading="{{ $resolvedLoading }}"
         decoding="async"
         @if ($resolvedFetchpriority) fetchpriority="{{ $resolvedFetchpriority }}" @endif
         {{ $attributes }}>
@endif
