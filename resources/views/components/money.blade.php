@props([
    'amount' => 0,
    'decimals' => null,
    'free' => true,
])

{{ \App\Support\Money::format($amount, $decimals, $free) }}
