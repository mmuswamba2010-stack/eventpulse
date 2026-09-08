@props([
    'amount' => 0,
    'decimals' => null,
    'free' => true,
    'dual' => null,
    'stacked' => false,
    'primary' => 'cdf',
    'onDark' => false,
])

@php
    $dual ??= ($primary === 'usd' && \App\Support\Money::usdEnabled());
    $amountCdf = (float) $amount;
    $primaryIsUsd = $primary === 'usd' && \App\Support\Money::usdEnabled();

    if ($primaryIsUsd) {
        $main = \App\Support\Money::formatUsd($amountCdf, $decimals, $free) ?? \App\Support\Money::format($amountCdf, $decimals, $free);
        $secondary = ($dual && $amountCdf > 0) ? \App\Support\Money::format($amountCdf, null, false) : null;
    } else {
        $main = \App\Support\Money::format($amountCdf, $decimals, $free);
        $secondary = ($dual && $amountCdf > 0) ? \App\Support\Money::formatUsd($amountCdf, $decimals, false) : null;
    }
@endphp

@if ($onDark && $secondary && $stacked)
    <span {{ $attributes->class(['inline-flex flex-col']) }}>
        <span class="font-extrabold text-white">{{ $main }}</span>
        <span class="text-base sm:text-lg font-semibold text-white/90">{{ $secondary }}</span>
    </span>
@elseif ($onDark && $secondary)
    <span {{ $attributes->class(['inline whitespace-nowrap']) }}>
        <span class="font-extrabold text-white">{{ $main }}</span><span class="text-sm font-semibold text-white/90"> · {{ $secondary }}</span>
    </span>
@elseif ($onDark)
    <span {{ $attributes->class(['font-extrabold text-white']) }}>{{ $main }}</span>
@elseif ($stacked && $secondary)
    <span {{ $attributes->class(['inline-flex flex-col items-end']) }}>
        <span class="font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $main }}</span>
        <span class="text-xs font-medium text-slate-600 dark:text-slate-300">{{ $secondary }}</span>
    </span>
@elseif ($secondary)
    <span {{ $attributes->class(['inline whitespace-nowrap']) }}>
        <span class="font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $main }}</span><span class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300"> · {{ $secondary }}</span>
    </span>
@else
    <span {{ $attributes->class(['text-charcoal dark:text-[#FAFAFA]']) }}>{{ $main }}</span>
@endif
