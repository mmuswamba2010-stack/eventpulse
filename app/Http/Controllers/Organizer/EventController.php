<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
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
            $data['image_path'] = $request->file('image')->store('events', 'public');
        }

        $event = DB::transaction(function () use ($data, $ticketTypes) {
            $event = Event::create($data);
            $this->syncTicketTypes($event, $ticketTypes);

            return $event;
        });

        if (Event::requiresPublicationPayment()) {
            return redirect()->route('organizer.events.pay', $event)
                ->with('success', 'Événement créé ! Réglez les frais de publication pour le rendre visible au public.');
        }

        return redirect()->route('organizer.events.index')
            ->with('success', 'Événement créé et publié avec succès !');
    }

    public function edit(Request $request, Event $event): View
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        $event->load('ticketTypes');

        return view('organizer.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);

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
            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }
            $data['image_path'] = $request->file('image')->store('events', 'public');
        }

        DB::transaction(function () use ($event, $data, $ticketTypes) {
            $event->update($data);
            $this->syncTicketTypes($event, $ticketTypes);
        });

        return redirect()->route('organizer.events.index')
            ->with('success', 'Événement mis à jour avec succès !');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        $soldTickets = $event->tickets()->where('status', '!=', 'cancelled')->count();

        if ($soldTickets > 0) {
            return back()->with('error', "Impossible de supprimer : {$soldTickets} billet(s) déjà vendu(s). Annulez l'événement à la place.");
        }

        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        $event->delete();

        return redirect()->route('organizer.events.index')
            ->with('success', 'Événement supprimé.');
    }

    public function pay(Request $request, Event $event): View|RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        if (! Event::requiresPublicationPayment()) {
            return redirect()->route('organizer.events.index')
                ->with('info', 'Le paiement de publication n\'est pas requis pour le moment.');
        }

        if ($event->is_paid) {
            return redirect()->route('organizer.events.index')
                ->with('info', 'Cet événement est déjà publié.');
        }

        return view('organizer.events.pay', compact('event'));
    }

    public function processPayment(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        if (! Event::requiresPublicationPayment()) {
            return redirect()->route('organizer.events.index')
                ->with('info', 'Le paiement de publication n\'est pas requis pour le moment.');
        }

        if ($event->is_paid) {
            return redirect()->route('organizer.events.index')
                ->with('info', 'Cet événement est déjà publié.');
        }

        $request->validate([
            'payment_method' => ['required', 'in:mobile_money'],
            'mobile_provider' => ['required', 'in:mpesa,orange_money,airtel_money'],
            'phone_number' => ['required', 'string', 'min:8', 'max:20'],
        ], [], [
            'payment_method' => 'moyen de paiement',
            'mobile_provider' => 'opérateur',
            'phone_number' => 'numéro de téléphone',
        ]);

        $event->update([
            'is_paid' => true,
            'status' => 'published',
            'payment_method' => $request->input('payment_method'),
            'paid_at' => now(),
        ]);

        return redirect()->route('organizer.events.index')
            ->with('success', 'Paiement effectué avec succès ! Votre événement est désormais publié.');
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
            'image' => ['nullable', 'image', 'max:4096'],
            'organizer_phone' => ['nullable', 'string', 'min:8', 'max:20'],
            'organizer_mobile_provider' => ['nullable', 'in:mpesa,orange_money,airtel_money'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:34'],
            'accepted_payment_methods' => $isFreeEvent
                ? ['nullable', 'array']
                : ['required', 'array', 'min:1'],
            'accepted_payment_methods.*' => ['in:mobile_money,card,cash'],
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
            'status' => 'statut',
            'image' => 'photo',
            'organizer_phone' => 'numéro Mobile Money',
            'organizer_mobile_provider' => 'opérateur Mobile Money',
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
            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                $name = $price <= 0 ? 'Entrée Gratuite' : 'Accès Général';
            }

            $quantity = ($row['quantity'] ?? '') === '' ? 100 : (int) $row['quantity'];

            $normalized[] = array_merge($row, [
                'name' => $name,
                'price' => max(0, $price),
                'quantity' => max(1, $quantity),
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

        if (in_array('mobile_money', $methods, true)) {
            $request->validate([
                'organizer_phone' => ['required', 'string', 'min:8', 'max:20'],
                'organizer_mobile_provider' => ['required', 'in:mpesa,orange_money,airtel_money'],
            ], [], [
                'organizer_phone' => 'numéro Mobile Money',
                'organizer_mobile_provider' => 'opérateur Mobile Money',
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
            'phone' => $request->input('organizer_phone') ?: $request->user()->phone,
            'mobile_money_provider' => $request->input('organizer_mobile_provider') ?: $request->user()->mobile_money_provider,
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
