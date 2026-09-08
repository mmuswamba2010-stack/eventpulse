<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Support\GuestCheckout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    /**
     * Réserver / acheter un billet pour un type de pass donné.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $newGuestAccount = false;

        if (! $request->user()) {
            $request->validate([
                'guest_name' => ['required', 'string', 'max:255'],
                'guest_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'guest_phone' => ['required', 'string', 'min:8', 'max:30'],
            ], [], [
                'guest_name' => __('Full name'),
                'guest_email' => __('Email address'),
                'guest_phone' => __('Phone'),
            ]);

            try {
                $user = GuestCheckout::resolveUser($request);
            } catch (ValidationException $e) {
                return back()->withInput()->withErrors($e->errors());
            }

            Auth::login($user);
            $request->setUserResolver(fn () => $user);
            $newGuestAccount = true;
        }

        $this->authorize('purchase', Ticket::class);

        /** @var TicketType|null $ticketType */
        $ticketType = $event->ticketTypes()->whereKey($request->integer('ticket_type_id'))->first();

        if (! $ticketType) {
            return back()->with('error', __('Invalid ticket type for this event.'));
        }
        if (! $ticketType->isPurchasable()) {
            return back()->with('error', __('Ticket type not available for sale.', ['name' => $ticketType->name]));
        }
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
            return back()->with('error', __('This event is no longer available for booking.'));
        }

        if ($request->user()->id === $event->user_id) {
            return back()->with('error', __('You cannot book tickets for your own event.'));
        }

        $alreadyHasTicket = Ticket::query()
            ->where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($alreadyHasTicket) {
            return back()->with('error', __('You already have a ticket for this event. Check My tickets.'));
        }

        $quantity = (int) $request->quantity;
        $paymentMethod = $isFreeTicket ? null : $request->string('payment_method')->toString();

        $createdTickets = [];

        try {
            DB::transaction(function () use ($event, $ticketType, $request, $quantity, $paymentMethod, &$createdTickets) {
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

                    $createdTickets[] = Ticket::create([
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
            return back()->with('error', $e->getMessage());
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
}
