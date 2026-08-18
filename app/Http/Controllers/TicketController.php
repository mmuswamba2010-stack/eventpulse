<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
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
    /**
     * Réserver / acheter un billet pour un type de pass donné.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        /** @var TicketType|null $ticketType */
        $ticketType = $event->ticketTypes()->whereKey($request->integer('ticket_type_id'))->first();

        if (! $ticketType) {
            return back()->with('error', 'Ce type de billet est invalide pour cet événement.');
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
            $rules['card_name'] = ['required_if:payment_method,card', 'nullable', 'string', 'max:255'];
            $rules['card_number'] = ['required_if:payment_method,card', 'nullable', 'string', 'min:12', 'max:23'];
            $rules['card_expiry'] = ['required_if:payment_method,card', 'nullable', 'string', 'max:7'];
            $rules['card_cvc'] = ['required_if:payment_method,card', 'nullable', 'string', 'min:3', 'max:4'];
        }

        $request->validate($rules, [], [
            'ticket_type_id' => 'type de billet',
            'quantity' => 'quantité',
            'payment_method' => 'mode de paiement',
            'mobile_provider' => 'opérateur',
            'phone_number' => 'numéro de téléphone',
            'card_name' => 'titulaire de la carte',
            'card_number' => 'numéro de carte',
            'card_expiry' => 'date d\'expiration',
            'card_cvc' => 'code de sécurité',
        ]);

        if (! $event->isPublished() || ! $event->isUpcoming()) {
            return back()->with('error', "Cet événement n'est plus disponible à la réservation.");
        }

        if ($request->user()->id === $event->user_id) {
            return back()->with('error', 'Vous ne pouvez pas réserver de billets pour votre propre événement.');
        }

        $quantity = (int) $request->quantity;
        $paymentMethod = $isFreeTicket ? null : $request->string('payment_method')->toString();
        $mobileProvider = $paymentMethod === 'mobile_money'
            ? ($request->input('mobile_provider') ?: 'orange_money')
            : null;

        // Simulation : on enregistre le mode choisi ; pas de prélèvement réel.
        try {
            DB::transaction(function () use ($event, $ticketType, $request, $quantity, $paymentMethod, $mobileProvider) {
                /** @var TicketType $lockedType */
                $lockedType = TicketType::whereKey($ticketType->id)->lockForUpdate()->firstOrFail();

                if ($lockedType->remainingSeats() < $quantity) {
                    throw new \RuntimeException(
                        'Il ne reste que '.$lockedType->remainingSeats().' place(s) pour « '.$lockedType->name.' ».'
                    );
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
                        'mobile_provider' => $mobileProvider,
                        'status' => 'valid',
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tickets.index')
            ->with('success', $isFreeTicket
                ? $quantity.' place(s) réservée(s) pour « '.$event->title.' » !'
                : $quantity.' billet(s) « '.$ticketType->name.' » réservé(s) pour « '.$event->title.' » !');
    }

    public function index(Request $request): View
    {
        $tickets = $request->user()->tickets()
            ->with(['event', 'ticketType'])
            ->latest()
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    public function show(Request $request, Ticket $ticket): View
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);

        $ticket->load(['event.user', 'ticketType', 'user']);

        $qrCode = base64_encode(
            QrCode::format('svg')->size(260)->margin(1)->generate($ticket->ticket_code)
        );

        return view('tickets.show', compact('ticket', 'qrCode'));
    }

    public function downloadPdf(Request $request, Ticket $ticket): Response
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);

        $ticket->load(['event.user', 'ticketType', 'user']);

        $qrCode = base64_encode(
            QrCode::format('svg')->size(220)->margin(1)->generate($ticket->ticket_code)
        );

        $pdf = Pdf::loadView('tickets.pdf', compact('ticket', 'qrCode'))
            ->setPaper('a5', 'portrait');

        return $pdf->download('billet-'.$ticket->id.'-event-pulse.pdf');
    }
}
