<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MobileMoneyPaymentService
{
    public function initiatePublicationPayment(Event $event, User $user, string $provider, string $phone): Payment
    {
        $payment = Payment::create([
            'reference' => Payment::generateReference(),
            'purpose' => Payment::PURPOSE_PUBLICATION,
            'payable_type' => Event::class,
            'payable_id' => $event->id,
            'user_id' => $user->id,
            'provider' => $provider,
            'phone' => $phone,
            'amount' => $event->publication_fee ?? Event::publicationFee(),
            'currency' => config('eventpulse.currency.code', 'CDF'),
            'status' => Payment::STATUS_PENDING,
            'metadata' => [
                'event_title' => $event->title,
            ],
        ]);

        if ($this->shouldSimulate()) {
            $this->confirm($payment, 'sim-'.uniqid());
        }

        return $payment->fresh();
    }

    /**
     * @param  list<Ticket>  $tickets
     */
    public function initiateTicketPayment(array $tickets, User $user, string $provider, string $phone, float $amount): Payment
    {
        $event = $tickets[0]->event;

        $payment = Payment::create([
            'reference' => Payment::generateReference(),
            'purpose' => Payment::PURPOSE_TICKET,
            'payable_type' => Event::class,
            'payable_id' => $event->id,
            'user_id' => $user->id,
            'provider' => $provider,
            'phone' => $phone,
            'amount' => $amount,
            'currency' => config('eventpulse.currency.code', 'CDF'),
            'status' => Payment::STATUS_PENDING,
            'metadata' => [
                'ticket_ids' => collect($tickets)->pluck('id')->all(),
                'event_title' => $event->title,
            ],
        ]);

        Ticket::query()
            ->whereIn('id', collect($tickets)->pluck('id'))
            ->update(['payment_id' => $payment->id]);

        if ($this->shouldSimulate()) {
            $this->confirm($payment, 'sim-'.uniqid());
        }

        return $payment->fresh();
    }

    public function confirm(Payment $payment, string $externalId, ?array $webhookPayload = null): Payment
    {
        if ($payment->isSucceeded()) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $externalId, $webhookPayload) {
            $payment->refresh();

            if ($payment->isSucceeded()) {
                return $payment;
            }

            $payment->update([
                'status' => Payment::STATUS_SUCCEEDED,
                'external_id' => $externalId,
                'confirmed_at' => now(),
                'webhook_received_at' => $webhookPayload ? now() : $payment->webhook_received_at,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'webhook' => $webhookPayload,
                ]),
            ]);

            if ($payment->purpose === Payment::PURPOSE_PUBLICATION) {
                $this->publishEvent($payment);
            }

            if ($payment->purpose === Payment::PURPOSE_TICKET) {
                $this->validateTickets($payment);
            }

            return $payment->fresh();
        });
    }

    public function fail(Payment $payment, ?string $reason = null): Payment
    {
        if ($payment->isSucceeded()) {
            return $payment;
        }

        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'metadata' => array_merge($payment->metadata ?? [], [
                'failure_reason' => $reason,
            ]),
        ]);

        if ($payment->purpose === Payment::PURPOSE_TICKET) {
            Ticket::query()
                ->where('payment_id', $payment->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
        }

        return $payment->fresh();
    }

    public function findByReference(string $reference): ?Payment
    {
        return Payment::query()->where('reference', $reference)->first();
    }

    public function shouldSimulate(): bool
    {
        return (bool) config('eventpulse.payment_simulation', true);
    }

    private function publishEvent(Payment $payment): void
    {
        $event = $payment->payable;

        if (! $event instanceof Event || $event->is_paid) {
            return;
        }

        $event->update([
            'is_paid' => true,
            'status' => 'published',
            'payment_method' => 'mobile_money',
            'paid_at' => now(),
        ]);
    }

    private function validateTickets(Payment $payment): void
    {
        Ticket::query()
            ->where('payment_id', $payment->id)
            ->where('status', 'pending')
            ->update(['status' => 'valid']);
    }
}
