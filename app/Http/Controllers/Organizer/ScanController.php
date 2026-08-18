<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScanController extends Controller
{
    /**
     * Page du scanner caméra.
     */
    public function index(): View
    {
        return view('organizer.scan');
    }

    /**
     * Valider un ticket scanné (appel AJAX).
     */
    public function validateTicket(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_code' => ['required', 'string'],
        ]);

        $organizer = $request->user();

        return DB::transaction(function () use ($request, $organizer) {
            $ticket = Ticket::with(['event', 'user'])
                ->where('ticket_code', $request->ticket_code)
                ->lockForUpdate()
                ->first();

            // Billet inexistant ou n'appartenant pas à un événement de cet organisateur.
            if (! $ticket || $ticket->event->user_id !== $organizer->id) {
                return response()->json([
                    'result' => 'invalid',
                    'message' => 'Billet invalide ou inconnu.',
                ], 404);
            }

            if ($ticket->status === 'used') {
                Scan::create([
                    'ticket_id' => $ticket->id,
                    'scanned_by' => $organizer->id,
                    'status' => 'already_used',
                ]);

                return response()->json([
                    'result' => 'already_used',
                    'message' => 'ALERTE : billet déjà utilisé le '.$ticket->scanned_at?->format('d/m/Y à H:i').' !',
                    'ticket' => $this->ticketPayload($ticket),
                ], 409);
            }

            if ($ticket->status === 'cancelled') {
                Scan::create([
                    'ticket_id' => $ticket->id,
                    'scanned_by' => $organizer->id,
                    'status' => 'invalid',
                ]);

                return response()->json([
                    'result' => 'invalid',
                    'message' => 'Ce billet a été annulé.',
                    'ticket' => $this->ticketPayload($ticket),
                ], 409);
            }

            if ($ticket->event->status === 'cancelled') {
                Scan::create([
                    'ticket_id' => $ticket->id,
                    'scanned_by' => $organizer->id,
                    'status' => 'invalid',
                ]);

                return response()->json([
                    'result' => 'invalid',
                    'message' => 'Cet événement est annulé.',
                    'ticket' => $this->ticketPayload($ticket),
                ], 409);
            }

            $updated = Ticket::whereKey($ticket->id)
                ->where('status', 'valid')
                ->update([
                    'status' => 'used',
                    'scanned_at' => now(),
                ]);

            if (! $updated) {
                $ticket->refresh();

                Scan::create([
                    'ticket_id' => $ticket->id,
                    'scanned_by' => $organizer->id,
                    'status' => 'already_used',
                ]);

                return response()->json([
                    'result' => 'already_used',
                    'message' => 'ALERTE : billet déjà utilisé !',
                    'ticket' => $this->ticketPayload($ticket->fresh(['event', 'user'])),
                ], 409);
            }

            Scan::create([
                'ticket_id' => $ticket->id,
                'scanned_by' => $organizer->id,
                'status' => 'success',
            ]);

            $ticket->refresh();

            return response()->json([
                'result' => 'success',
                'message' => 'Billet valide. Entrée autorisée !',
                'ticket' => $this->ticketPayload($ticket),
            ]);
        });
    }

    /**
     * @return array<string, string>
     */
    private function ticketPayload(Ticket $ticket): array
    {
        return [
            'holder' => $ticket->user->name,
            'event' => $ticket->event->title,
            'event_date' => $ticket->event->event_date->format('d/m/Y H:i'),
        ];
    }
}
