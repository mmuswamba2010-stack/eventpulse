<?php

namespace App\Support;

class Money
{
    public static function format(float|int|null $amount, ?int $decimals = null, bool $free = true): string
    {
        if ($free && (float) $amount <= 0) {
            return __('Free');
        }

        $decimals ??= (int) config('eventpulse.currency.decimals', 0);
        $formatted = number_format((float) $amount, $decimals, ',', ' ');
        $symbol = config('eventpulse.currency.symbol', 'FC');

        return config('eventpulse.currency.symbol_position', 'after') === 'before'
            ? $symbol.' '.$formatted
            : $formatted.' '.$symbol;
    }

    public static function formatUsd(float|int|null $amountInCdf, ?int $decimals = null, bool $free = true): ?string
    {
        if (! self::usdEnabled()) {
            return null;
        }

        $usd = self::cdfToUsd((float) $amountInCdf);

        if ($free && (float) $amountInCdf <= 0) {
            return null;
        }

        return self::formatUsdValue($usd, $decimals, false);
    }

    public static function formatUsdValue(float|int|null $amountUsd, ?int $decimals = null, bool $free = true): string
    {
        if ($free && (float) $amountUsd <= 0) {
            return __('Free');
        }

        $decimals ??= (int) config('eventpulse.usd.decimals', 2);
        $formatted = number_format((float) $amountUsd, $decimals, ',', ' ');
        $symbol = (string) config('eventpulse.usd.symbol', '$');

        return config('eventpulse.usd.symbol_position', 'before') === 'before'
            ? $symbol.' '.$formatted
            : $formatted.' '.$symbol;
    }

    public static function usdEnabled(): bool
    {
        return (bool) config('eventpulse.usd.enabled', false);
    }

    public static function cdfPerUsd(): float
    {
        return (float) config('eventpulse.usd.cdf_per_usd', 0);
    }

    public static function usdToCdf(float $usd): float
    {
        $rate = self::cdfPerUsd();

        if ($rate <= 0) {
            return $usd;
        }

        return round($usd * $rate, (int) config('eventpulse.currency.decimals', 0));
    }

    public static function cdfToUsd(float $cdf): float
    {
        $rate = self::cdfPerUsd();

        if ($rate <= 0) {
            return $cdf;
        }

        return round($cdf / $rate, (int) config('eventpulse.usd.decimals', 2));
    }

    public static function symbol(): string
    {
        return (string) config('eventpulse.currency.symbol', 'FC');
    }

    public static function usdSymbol(): string
    {
        return (string) config('eventpulse.usd.symbol', '$');
    }

    public static function name(): string
    {
        return (string) config('eventpulse.currency.name', 'Franc congolais');
    }
}
