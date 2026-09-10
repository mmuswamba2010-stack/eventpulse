<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Support\PendingTicketCheckout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    public function choose(Event $event): View|RedirectResponse
    {
        if (! $event->isPublished() || ! $event->isUpcoming()) {
            return redirect()
                ->route('events.show', $event->slug)
                ->with('error', __('This event is no longer available for booking.'));
        }

        $event->load(['user', 'ticketTypes']);
        $event->loadCount(['tickets' => fn ($q) => $q->where('status', '!=', 'cancelled')]);

        $types = $event->ticketTypes->sortBy('price');
        $purchasableTypes = $event->purchasableTicketTypes();
        $remaining = $event->remainingSeats();
        $isFreeEvent = $event->isFreeEvent();

        $alreadyBooked = auth()->check()
            ? $event->tickets()->where('user_id', auth()->id())->where('status', '!=', 'cancelled')->exists()
            : false;

        if ($remaining <= 0) {
            return redirect()
                ->route('events.show', $event->slug)
                ->with('error', __('Sold out'));
        }

        if ($types->isEmpty()) {
            return redirect()
                ->route('events.show', $event->slug)
                ->with('error', __('No ticket types configured'));
        }

        return view('tickets.choose', compact(
            'event',
            'types',
            'purchasableTypes',
            'remaining',
            'isFreeEvent',
            'alreadyBooked',
        ));
    }

    /**
     * Choix du pass sur la page billetterie → checkout dédié.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $ticketType = $this->resolveTicketType($event, $request);

        if (! $ticketType) {
            return back()->with('error', __('Invalid ticket type for this event.'));
        }

        if (! $ticketType->isPurchasable()) {
            return back()->with('error', __('Ticket type not available for sale.', ['name' => $ticketType->name]));
        }

        if (! $event->isPublished() || ! $event->isUpcoming()) {
            return back()->with('error', __('This event is no longer available for booking.'));
        }

        PendingTicketCheckout::store(
            $event,
            $ticketType->id,
            (int) $request->quantity,
        );

        return redirect()->route('tickets.checkout.confirm', $event);
    }

    public function confirmCheckout(Event $event): View|RedirectResponse
    {
        if (! PendingTicketCheckout::matchesEvent($event)) {
            return redirect()
                ->route('tickets.choose', $event)
                ->with('info', __('Paid checkout session expired'));
        }

        $pending = PendingTicketCheckout::get();
        $ticketType = $event->ticketTypes()->whereKey($pending['ticket_type_id'])->first();

        if (! $ticketType || ! $ticketType->isPurchasable()) {
            PendingTicketCheckout::forget();

            return redirect()
                ->route('tickets.choose', $event)
                ->with('error', __('Ticket type not available for sale.', ['name' => $ticketType?->name ?? '']));
        }

        $isFreeTicket = (float) $ticketType->price <= 0;

        if (auth()->guest()) {
            PendingTicketCheckout::rememberIntendedUrl($event);
        }

        return view('tickets.checkout-confirm', [
            'event' => $event->load('user'),
            'ticketType' => $ticketType,
            'quantity' => (int) $pending['quantity'],
            'paymentMethod' => $pending['payment_method'],
            'isFreeTicket' => $isFreeTicket,
            'acceptedPayments' => $event->acceptedPaymentMethods(),
        ]);
    }

    public function updateCheckoutPayment(Request $request, Event $event): RedirectResponse
    {
        if (! PendingTicketCheckout::matchesEvent($event)) {
            return redirect()
                ->route('tickets.choose', $event)
                ->with('info', __('Paid checkout session expired'));
        }

        $allowedMethods = $event->acceptedPaymentMethods();

        $request->validate([
            'payment_method' => ['required', Rule::in($allowedMethods)],
        ]);

        PendingTicketCheckout::updatePaymentMethod($request->string('payment_method')->toString());

        return redirect()->route('tickets.checkout.confirm', $event);
    }

    public function completeCheckout(Request $request, Event $event): RedirectResponse
    {
        if (! PendingTicketCheckout::matchesEvent($event)) {
            return redirect()
                ->route('tickets.choose', $event)
                ->with('info', __('Paid checkout session expired'));
        }

        $pending = PendingTicketCheckout::get();
        $ticketType = $event->ticketTypes()->whereKey($pending['ticket_type_id'])->first();

        if (! $ticketType || ! $ticketType->isPurchasable()) {
            PendingTicketCheckout::forget();

            return redirect()
                ->route('tickets.choose', $event)
                ->with('error', __('Ticket type not available for sale.', ['name' => $ticketType?->name ?? '']));
        }

        $isFreeTicket = (float) $ticketType->price <= 0;
        $paymentMethod = $pending['payment_method'];

        if (! $request->user()) {
            PendingTicketCheckout::rememberIntendedUrl($event);

            return redirect()
                ->route('login')
                ->with('info', $isFreeTicket
                    ? __('Free checkout login required')
                    : __('Paid checkout login required'));
        }

        if (! $isFreeTicket) {
            $request->validate([
                'payment_method' => ['required', Rule::in($event->acceptedPaymentMethods())],
            ]);

            $paymentMethod = $request->string('payment_method')->toString();
            PendingTicketCheckout::updatePaymentMethod($paymentMethod);
        }

        $request->merge([
            'ticket_type_id' => $pending['ticket_type_id'],
            'quantity' => $pending['quantity'],
            'payment_method' => $paymentMethod,
        ]);

        return $this->finalizeBooking($request, $event, $ticketType, false);
    }

    public function index(Request $request): View
    {
        $tickets = $request->user()->tickets()
            ->with(['event', 'ticketType', 'payment'])
            ->latest()
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['event.user', 'ticketType', 'user', 'payment']);

        $qrCode = base64_encode(
            QrCode::format('svg')->size(260)->margin(1)->generate($ticket->ticket_code)
        );

        return view('tickets.show', compact('ticket', 'qrCode'));
    }

    public function downloadPdf(Request $request, Ticket $ticket): Response
    {
        $this->authorize('download', $ticket);

        $ticket->load(['event.user', 'ticketType', 'user']);

        $qrCode = base64_encode(
            QrCode::format('svg')->size(220)->margin(1)->generate($ticket->ticket_code)
        );

        $pdf = Pdf::loadView('tickets.pdf', compact('ticket', 'qrCode'))
            ->setPaper('a5', 'portrait');

        return $pdf->download('billet-'.$ticket->id.'-event-pulse.pdf');
    }

    private function finalizeBooking(
        Request $request,
        Event $event,
        TicketType $ticketType,
        bool $newGuestAccount,
    ): RedirectResponse {
        $this->authorize('purchase', Ticket::class);

        $isFreeTicket = (float) $ticketType->price <= 0;

        $rules = [
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ];

        if (! $isFreeTicket) {
            $allowedMethods = $event->acceptedPaymentMethods();

            $rules['payment_method'] = ['required', Rule::in($allowedMethods)];
        }

        $request->validate($rules);

        if (! $event->isPublished() || ! $event->isUpcoming()) {
            return redirect()
                ->route('tickets.checkout.confirm', $event)
                ->with('error', __('This event is no longer available for booking.'));
        }

        if ($request->user()->id === $event->user_id) {
            return redirect()
                ->route('tickets.checkout.confirm', $event)
                ->with('error', __('You cannot book tickets for your own event.'));
        }

        $alreadyHasTicket = Ticket::query()
            ->where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($alreadyHasTicket) {
            return redirect()
                ->route('tickets.checkout.confirm', $event)
                ->with('error', __('You already have a ticket for this event. Check My tickets.'));
        }

        $quantity = (int) $request->quantity;
        $paymentMethod = $isFreeTicket ? null : $request->string('payment_method')->toString();

        try {
            DB::transaction(function () use ($event, $ticketType, $request, $quantity, $paymentMethod) {
                /** @var TicketType $lockedType */
                $lockedType = TicketType::whereKey($ticketType->id)->lockForUpdate()->firstOrFail();

                if ($lockedType->remainingSeats() < $quantity) {
                    throw new \RuntimeException(__('Only :count seat(s) left for :name.', [
                        'count' => $lockedType->remainingSeats(),
                        'name' => $lockedType->name,
                    ]));
                }

                for ($i = 0; $i < $quantity; $i++) {
                    $seatNumber = null;
                    if ($lockedType->is_seated || $event->isSeatedPlacement()) {
                        $seatNumber = $lockedType->nextSeatLabel();
                    }

                    Ticket::create([
                        'event_id' => $event->id,
                        'ticket_type_id' => $lockedType->id,
                        'user_id' => $request->user()->id,
                        'ticket_code' => Ticket::generateUniqueCode(),
                        'ticket_number' => Ticket::generateUniqueTicketNumber(),
                        'seat_number' => $seatNumber,
                        'payment_method' => $paymentMethod,
                        'status' => 'valid',
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('tickets.checkout.confirm', $event)
                ->with('error', $e->getMessage());
        }

        if (PendingTicketCheckout::matchesEvent($event)) {
            PendingTicketCheckout::forget();
        }

        $redirect = redirect()->route('tickets.index')
            ->with('success', $isFreeTicket
                ? __('Places booked success', ['count' => $quantity, 'title' => $event->title])
                : __('Tickets booked success', ['count' => $quantity, 'name' => $ticketType->name, 'title' => $event->title]));

        if ($newGuestAccount) {
            $redirect->with('info', __('Guest checkout account created'));
        }

        return $redirect;
    }

    private function resolveTicketType(Event $event, Request $request): ?TicketType
    {
        return $event->ticketTypes()->whereKey($request->integer('ticket_type_id'))->first();
    }
}
