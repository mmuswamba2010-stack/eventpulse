@props(['category'])

@php
    $accent = match ($category) {
        'music' => 'bg-coral-muted text-coral',
        'tech', 'conference' => 'bg-violet-muted text-violet',
        'sport' => 'bg-coral text-white',
        'art' => 'bg-violet-muted text-violet',
        default => 'bg-cream text-frost border border-charcoal/10',
    };
    $label = \App\Models\Event::CATEGORIES[$category] ?? 'Autre';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold {$accent}"]) }}>
    {{ $label }}
</span>
