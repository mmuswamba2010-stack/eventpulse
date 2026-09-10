<?php

namespace App\Support;

use App\Models\Event;

class PendingTicketCheckout
{
    private const SESSION_KEY = 'pending_ticket_checkout';

    public static function store(Event $event, int $ticketTypeId, int $quantity, ?string $paymentMethod = null): void
    {
        session([
            self::SESSION_KEY => [
                'event_id' => $event->id,
                'ticket_type_id' => $ticketTypeId,
                'quantity' => $quantity,
                'payment_method' => $paymentMethod,
            ],
        ]);
    }

    public static function updatePaymentMethod(string $paymentMethod): void
    {
        $pending = self::get();

        if ($pending === null) {
            return;
        }

        $pending['payment_method'] = $paymentMethod;
        session([self::SESSION_KEY => $pending]);
    }

    /**
     * @return array{event_id: int, ticket_type_id: int, quantity: int, payment_method: string|null}|null
     */
    public static function get(): ?array
    {
        $pending = session(self::SESSION_KEY);

        return is_array($pending) ? $pending : null;
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function matchesEvent(Event $event): bool
    {
        $pending = self::get();

        return $pending !== null && (int) $pending['event_id'] === $event->id;
    }

    public static function rememberIntendedUrl(Event $event): void
    {
        session()->put('url.intended', route('tickets.checkout.confirm', $event));
    }
}
