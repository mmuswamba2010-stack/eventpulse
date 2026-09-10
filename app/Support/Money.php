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

    /**
     * Prix catalogue : une seule devise, lisible (pas de « 0,04 $ · 80 FC »).
     */
    public static function formatCatalog(float|int|null $amountInCdf, bool $free = true): string
    {
        if ($free && (float) $amountInCdf <= 0) {
            return __('Free');
        }

        $mode = (string) config('eventpulse.catalog.price_currency', 'auto');

        if ($mode === 'cdf') {
            return self::format($amountInCdf, null, false);
        }

        if ($mode === 'usd' && self::usdEnabled()) {
            return self::formatUsdValue(round(self::amountAsUsd($amountInCdf)), 0, false);
        }

        if ($mode === 'auto' && self::usdEnabled()) {
            $usd = self::amountAsUsd($amountInCdf);

            if ($usd >= 1) {
                return self::formatUsdValue(round($usd), 0, false);
            }
        }

        return self::format($amountInCdf, null, false);
    }

    /**
     * Montant stocké → valeur USD pour affichage.
     * ≥ 2 250 en base = francs (saisie organisateur convertie) · sinon = dollars.
     */
    public static function amountAsUsd(float|int|null $stored): float
    {
        $stored = (float) $stored;

        if (! self::usdEnabled() || self::cdfPerUsd() <= 0) {
            return $stored;
        }

        if ($stored >= self::cdfPerUsd()) {
            return self::cdfToUsd($stored);
        }

        return $stored;
    }

    /**
     * Fiche événement — billets à acheter affichés en dollars (sans double devise).
     */
    public static function formatEventPrice(float|int|null $amountInCdf, bool $free = true): string
    {
        if ($free && (float) $amountInCdf <= 0) {
            return __('Free');
        }

        if (self::usdEnabled()) {
            return self::formatUsdValue(round(self::amountAsUsd($amountInCdf)), 0, false);
        }

        return self::format($amountInCdf, null, false);
    }

    /**
     * Page choix billet — ex. « 80 dollars ».
     */
    public static function formatEventPriceLabel(float|int|null $stored, bool $free = true): string
    {
        if ($free && (float) $stored <= 0) {
            return __('Free');
        }

        if (self::usdEnabled()) {
            $usd = number_format(round(self::amountAsUsd($stored)), 0, ',', ' ');

            return __('Ticket price in dollars', ['amount' => $usd]);
        }

        return self::format($stored, null, false);
    }
}
