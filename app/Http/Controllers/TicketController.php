<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\MobileMoneyPaymentService;
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
    public function __construct(
        private MobileMoneyPaymentService $payments,
    ) {}

    /**
     * Réserver / acheter un billet pour un type de pass donné.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('purchase', Ticket::class);

        /** @var TicketType|null $ticketType */
        $ticketType = $event->ticketTypes()->whereKey($request->integer('ticket_type_id'))->first();

        if (! $ticketType) {
            return back()->with('error', __('Invalid ticket type for this event.'));
        }

        $isFreeTicket = (float) $ticketType->price <= 0;

        $rules = [
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ];

        if (! $isFreeTicket) {
            $allowedMethods = $event->acceptedPaymentMethods();

            $rules['payment_method'] = ['required', Rule::in($allowedMethods)];
            $rules['mobile_provider'] = ['required_if:payment_method,mobile_money', 'nullable', 'in:mpesa,orange_money,airtel_money'];
            $rules['phone_number'] = ['required_if:payment_method,mobile_money', 'nullable', 'string', 'min:8', 'max:20'];
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
        $mobileProvider = $paymentMethod === 'mobile_money'
            ? ($request->input('mobile_provider') ?: 'orange_money')
            : null;
        $phoneNumber = $request->input('phone_number');

        $awaitingMobileMoney = ! $isFreeTicket
            && $paymentMethod === 'mobile_money'
            && ! $this->payments->shouldSimulate();

        $initialStatus = $awaitingMobileMoney ? 'pending' : 'valid';

        $createdTickets = [];

        try {
            DB::transaction(function () use ($event, $ticketType, $request, $quantity, $paymentMethod, $mobileProvider, $initialStatus, &$createdTickets) {
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
                        'mobile_provider' => $mobileProvider,
                        'status' => $initialStatus,
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($awaitingMobileMoney) {
            $amount = (float) $ticketType->price * $quantity;
            $payment = $this->payments->initiateTicketPayment(
                $createdTickets,
                $request->user(),
                (string) $mobileProvider,
                (string) $phoneNumber,
                $amount,
            );

            if ($payment->isPending()) {
                return redirect()
                    ->route('payments.show', $payment)
                    ->with('info', __('Payment pending mobile money confirmation.'));
            }
        }

        return redirect()->route('tickets.index')
            ->with('success', $isFreeTicket
                ? __('Places booked success', ['count' => $quantity, 'title' => $event->title])
                : __('Tickets booked success', ['count' => $quantity, 'name' => $ticketType->name, 'title' => $event->title]));
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
