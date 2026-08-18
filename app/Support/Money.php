<?php

namespace App\Support;

class Money
{
    public static function format(float|int|null $amount, ?int $decimals = null, bool $free = true): string
    {
        if ($free && (float) $amount <= 0) {
            return 'Gratuit';
        }

        $decimals ??= (int) config('eventpulse.currency.decimals', 0);
        $formatted = number_format((float) $amount, $decimals, ',', ' ');
        $symbol = config('eventpulse.currency.symbol', 'FC');

        return config('eventpulse.currency.symbol_position', 'after') === 'before'
            ? $symbol.' '.$formatted
            : $formatted.' '.$symbol;
    }

    public static function symbol(): string
    {
        return (string) config('eventpulse.currency.symbol', 'FC');
    }

    public static function name(): string
    {
        return (string) config('eventpulse.currency.name', 'Franc congolais');
    }
}
