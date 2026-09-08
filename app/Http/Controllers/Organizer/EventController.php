<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\PublicationPaymentService;
use App\Support\EventImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private PublicationPaymentService $payments,
    ) {}
    public function index(Request $request): View
    {
        $events = $request->user()->events()
            ->withCount(['tickets as sold_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->orderByDesc('event_date')
            ->paginate(10);

        return view('organizer.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('organizer.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, forCreate: true);
        $ticketTypes = $data['ticket_types'];
        unset($data['ticket_types']);

        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['status'] = Event::requiresPublicationPayment() ? 'draft' : 'published';
        $data['is_paid'] = false;
        $data['publication_fee'] = Event::publicationFee();
        $data['capacity'] = collect($ticketTypes)->sum('quantity');
        $data['price'] = collect($ticketTypes)->min('price');
        $data['accepted_payment_methods'] = $this->normalizeAcceptedPaymentMethods($request, $ticketTypes);

        if (! Event::ticketTypesAreFree($ticketTypes)) {
            $this->syncOrganizerPaymentProfile($request);
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = EventImage::storeFromUpload($request->file('image'));
        }

        $event = DB::transaction(function () use ($data, $ticketTypes) {
            $event = Event::create($data);
            $this->syncTicketTypes($event, $ticketTypes);

            return $event;
        });

        if (Event::requiresPublicationPayment()) {
            return redirect()->route('organizer.events.pay', $event)
                ->with('success', __('Event created pay to publish'));
        }

        return redirect()->route('organizer.events.index')
            ->with('success', __('Event created and published'));
    }

    public function edit(Request $request, Event $event): View
    {
        $this->authorize('update', $event);

        $event->load('ticketTypes');

        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $data = $this->validated($request, forCreate: false, event: $event);
        $ticketTypes = $data['ticket_types'];
        unset($data['ticket_types']);

        if (Event::requiresPublicationPayment()
            && ($data['status'] ?? null) === 'published'
            && ! $event->is_paid) {
            $data['status'] = 'draft';
        }

        if ($data['title'] !== $event->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $event->id);
        }

        $data['capacity'] = collect($ticketTypes)->sum('quantity');
        $data['price'] = collect($ticketTypes)->min('price');
        $data['accepted_payment_methods'] = $this->normalizeAcceptedPaymentMethods($request, $ticketTypes);

        if (! Event::ticketTypesAreFree($ticketTypes)) {
            $this->syncOrganizerPaymentProfile($request);
        }

        if ($request->hasFile('image')) {
            EventImage::delete($event->image_path);
            $data['image_path'] = EventImage::storeFromUpload($request->file('image'));
        }

        DB::transaction(function () use ($event, $data, $ticketTypes) {
            $event->update($data);
            $this->syncTicketTypes($event, $ticketTypes);
        });

        return redirect()->route('organizer.events.index')
            ->with('success', __('Event updated success'));
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $soldTickets = $event->tickets()->where('status', '!=', 'cancelled')->count();

        if ($soldTickets > 0) {
            return back()->with('error', __('Event delete blocked sold', ['count' => $soldTickets]));
        }

        EventImage::delete($event->image_path);

        $event->delete();

        return redirect()->route('organizer.events.index')
            ->with('success', __('Event deleted success'));
    }

    public function pay(Request $request, Event $event): View|RedirectResponse
    {
        $this->authorize('pay', $event);

        if (! Event::requiresPublicationPayment()) {
            return redirect()->route('organizer.events.index')
                ->with('info', __('Publication payment not required'));
        }

        if ($event->is_paid) {
            return redirect()->route('organizer.events.index')
                ->with('info', __('Event already published paid'));
        }

        return view('organizer.events.pay', compact('event'));
    }

    public function processPayment(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('pay', $event);

        if (! Event::requiresPublicationPayment()) {
            return redirect()->route('organizer.events.index')
                ->with('info', __('Publication payment not required'));
        }

        if ($event->is_paid) {
            return redirect()->route('organizer.events.index')
                ->with('info', __('Event already published paid'));
        }

        $payment = $this->payments->initiatePublicationPayment($event, $request->user());

        if ($payment->isPending()) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('info', __('Payment pending confirmation.'));
        }

        return redirect()->route('organizer.events.index')
            ->with('success', __('Publication payment success'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $forCreate = false, ?Event $event = null): array
    {
        if (! Event::allowsSeatedPlacement()) {
            $placement = $event && $event->isSeatedPlacement()
                ? Event::PLACEMENT_SEATED
                : Event::PLACEMENT_STANDING;

            $request->merge(['placement_mode' => $placement]);
        }

        $this->normalizeTicketTypesInput($request);

        $isFreeEvent = Event::ticketTypesAreFree($request->input('ticket_types', []));

        if ($isFreeEvent) {
            $request->merge(['accepted_payment_methods' => []]);
        }

        $placementModes = Event::allowsSeatedPlacement()
            ? 'standing,seated'
            : Event::PLACEMENT_STANDING;

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:'.implode(',', Event::categoryKeys())],
            'event_date' => ['required', 'date', 'after:now'],
            'placement_mode' => ['required', 'in:'.$placementModes],
            'ticket_types' => ['required', 'array', 'min:1'],
            'ticket_types.*.id' => ['nullable', 'integer'],
            'ticket_types.*.name' => ['nullable', 'string', 'max:100'],
            'ticket_types.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'ticket_types.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'ticket_types.*.sale_starts_at' => ['nullable', 'date'],
            'ticket_types.*.sale_ends_at' => ['nullable', 'date'],
            'ticket_types.*.is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:34'],
            'accepted_payment_methods' => $isFreeEvent
                ? ['nullable', 'array']
                : ['required', 'array', 'min:1'],
            'accepted_payment_methods.*' => ['in:card,cash'],
        ];

        if (! $forCreate) {
            if (Event::requiresPublicationPayment() && $event && ! $event->is_paid) {
                $allowedStatuses = ['draft', 'cancelled'];
            } else {
                $allowedStatuses = ['published', 'draft', 'cancelled'];
            }

            $rules['status'] = ['required', 'in:'.implode(',', $allowedStatuses)];
        }

        return $request->validate($rules, [], [
            'title' => 'titre',
            'description' => 'description',
            'location' => 'lieu',
            'category' => 'catégorie',
            'event_date' => 'date de l\'événement',
            'placement_mode' => 'type de placement',
            'ticket_types' => 'types de billets',
            'ticket_types.*.name' => 'nom du pass',
            'ticket_types.*.price' => 'prix',
            'ticket_types.*.quantity' => 'quantité',
            'ticket_types.*.sale_starts_at' => 'début de vente',
            'ticket_types.*.sale_ends_at' => 'fin de vente',
            'ticket_types.*.is_active' => 'vente active',
            'status' => 'statut',
            'image' => 'photo',
            'bank_account_holder' => 'titulaire du compte',
            'bank_name' => 'banque',
            'bank_account_number' => 'RIB / IBAN',
            'accepted_payment_methods' => 'moyens de paiement acceptés',
        ]);
    }

    /**
     * Valeurs par défaut pour les passes laissés vides (nom, prix).
     */
    private function normalizeTicketTypesInput(Request $request): void
    {
        $types = $request->input('ticket_types');

        if (! is_array($types)) {
            return;
        }

        $normalized = [];

        foreach ($types as $row) {
            if (! is_array($row)) {
                continue;
            }

            $price = ($row['price'] ?? '') === '' ? 0.0 : (float) $row['price'];

            if (\App\Support\Money::usdEnabled() && \App\Support\Money::cdfPerUsd() > 0) {
                $price = \App\Support\Money::usdToCdf($price);
            }

            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                $name = $price <= 0 ? 'Entrée Gratuite' : 'Accès Général';
            }

            $quantity = ($row['quantity'] ?? '') === '' ? 100 : (int) $row['quantity'];

            $saleStartsAt = filled($row['sale_starts_at'] ?? null) ? $row['sale_starts_at'] : null;
            $saleEndsAt = filled($row['sale_ends_at'] ?? null) ? $row['sale_ends_at'] : null;

            if ($saleStartsAt && $saleEndsAt && strtotime($saleEndsAt) < strtotime($saleStartsAt)) {
                throw ValidationException::withMessages([
                    'ticket_types' => __('Ticket sale end before start', ['name' => $name]),
                ]);
            }

            $normalized[] = array_merge($row, [
                'name' => $name,
                'price' => max(0, $price),
                'quantity' => max(1, $quantity),
                'sale_starts_at' => $saleStartsAt,
                'sale_ends_at' => $saleEndsAt,
                'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ]);
        }

        $request->merge(['ticket_types' => $normalized]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $ticketTypes
     * @return list<string>
     */
    private function normalizeAcceptedPaymentMethods(Request $request, array $ticketTypes): array
    {
        if (Event::ticketTypesAreFree($ticketTypes)) {
            return [];
        }

        $methods = array_values(array_unique($request->input('accepted_payment_methods', [])));

        if ($methods === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'accepted_payment_methods' => 'Sélectionnez au moins un moyen de paiement pour un événement payant.',
            ]);
        }

        if (in_array('card', $methods, true)) {
            $request->validate([
                'bank_account_holder' => ['required', 'string', 'max:255'],
                'bank_name' => ['required', 'string', 'max:255'],
                'bank_account_number' => ['required', 'string', 'min:8', 'max:34'],
            ], [], [
                'bank_account_holder' => 'titulaire du compte',
                'bank_name' => 'banque',
                'bank_account_number' => 'RIB / IBAN',
            ]);
        }

        return $methods;
    }

    private function syncOrganizerPaymentProfile(Request $request): void
    {
        if (! $request->user()->isOrganizer()) {
            return;
        }

        $request->user()->update([
            'bank_account_holder' => $request->input('bank_account_holder') ?: $request->user()->bank_account_holder,
            'bank_name' => $request->input('bank_name') ?: $request->user()->bank_name,
            'bank_account_number' => $request->input('bank_account_number') ?: $request->user()->bank_account_number,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $ticketTypes
     */
    private function syncTicketTypes(Event $event, array $ticketTypes): void
    {
        $isSeated = $event->placement_mode === Event::PLACEMENT_SEATED;
        $keptIds = [];

        foreach ($ticketTypes as $row) {
            $payload = [
                'name' => $row['name'],
                'price' => $row['price'],
                'quantity' => (int) $row['quantity'],
                'is_seated' => $isSeated,
                'sale_starts_at' => $row['sale_starts_at'] ?? null,
                'sale_ends_at' => $row['sale_ends_at'] ?? null,
                'is_active' => (bool) ($row['is_active'] ?? true),
            ];

            if (! empty($row['id'])) {
                $type = $event->ticketTypes()->whereKey($row['id'])->first();
                if ($type) {
                    $sold = $type->soldCount();
                    if ($payload['quantity'] < $sold) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'ticket_types' => "La quantité du pass « {$type->name} » ne peut pas être inférieure aux {$sold} billet(s) déjà vendu(s).",
                        ]);
                    }

                    $type->update($payload);
                    $keptIds[] = $type->id;

                    continue;
                }
            }

            $created = $event->ticketTypes()->create($payload);
            $keptIds[] = $created->id;
        }

        $event->ticketTypes()
            ->whereNotIn('id', $keptIds)
            ->whereDoesntHave('tickets')
            ->delete();
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (Event::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
