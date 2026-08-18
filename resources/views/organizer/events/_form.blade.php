@php
    /** @var \App\Models\Event|null $event */
    $event = $event ?? null;
    $oldTypes = old('ticket_types');
    if ($oldTypes === null && $event) {
        $oldTypes = $event->ticketTypes->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'price' => (string) $t->price,
            'quantity' => $t->quantity,
        ])->values()->all();
    }
    if (empty($oldTypes)) {
        $oldTypes = [['id' => null, 'name' => 'Accès Général', 'price' => '0', 'quantity' => 100]];
    }
    $currentPlacement = old('placement_mode', $event?->placement_mode ?? 'standing');
    if (! \App\Models\Event::allowsSeatedPlacement()) {
        $currentPlacement = ($event && $event->isSeatedPlacement())
            ? \App\Models\Event::PLACEMENT_SEATED
            : \App\Models\Event::PLACEMENT_STANDING;
    }
    $seatedPlacementEnabled = \App\Models\Event::allowsSeatedPlacement();
    $defaultMethods = old('accepted_payment_methods', $event?->accepted_payment_methods ?? ['mobile_money', 'card', 'cash']);
    if (! is_array($defaultMethods)) {
        $defaultMethods = ['mobile_money', 'card', 'cash'];
    }
    if ($event && ! $event->isFreeEvent() && $defaultMethods === []) {
        $defaultMethods = ['mobile_money', 'cash'];
    }
@endphp

<div class="space-y-8"
     x-data="{
        placement: @js($currentPlacement),
        types: @js($oldTypes),
        methods: @js(array_values($defaultMethods)),
        addType() {
            this.types.push({ id: null, name: 'Accès Général', price: '0', quantity: 100 });
        },
        removeType(index) {
            if (this.types.length > 1) this.types.splice(index, 1);
        },
        toggles(method) { return this.methods.includes(method); },
        toggle(method) {
            if (this.methods.includes(method)) {
                if (!this.isFreeEvent && this.methods.length <= 1) return;
                this.methods = this.methods.filter(m => m !== method);
            } else {
                this.methods.push(method);
            }
        },
        ensurePaidMethods() {
            if (!this.isFreeEvent && this.methods.length === 0) {
                this.methods = ['mobile_money', 'cash'];
            }
        },
        get isFreeEvent() {
            return this.types.length > 0 && this.types.every(t => parseFloat(t.price || 0) <= 0);
        }
     }"
     x-init="$watch('isFreeEvent', () => ensurePaidMethods())">
    <!-- Informations générales -->
    <div>
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">
            <x-icon name="sparkles" class="w-4 h-4 text-brand" /> Informations générales
        </h3>
        <div class="grid grid-cols-1 gap-5">
            <div>
                <x-input-label for="title" value="Titre de l'événement *" />
                <x-text-input id="title" name="title" type="text" class="mt-1.5 block w-full"
                    :value="old('title', $event?->title)" placeholder="Ex. Festival des Lumières" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="category" value="Catégorie *" />
                <select id="category" name="category" required
                    class="mt-1.5 ep-input">
                    @foreach (\App\Models\Event::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $event?->category ?? 'other') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Description *" />
                <textarea id="description" name="description" rows="5" required placeholder="Décrivez votre événement en détail..."
                    class="mt-1.5 block w-full border-slate-200 bg-slate-50/60 focus:bg-white focus:border-brand focus:ring-brand/40 rounded-xl shadow-sm text-sm placeholder:text-slate-400 transition">{{ old('description', $event?->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
    </div>

    <!-- Lieu & horaires -->
    <div>
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">
            <x-icon name="map-pin" class="w-4 h-4 text-brand" /> Lieu &amp; horaires
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <x-input-label for="location" value="Lieu *" />
                <x-text-input id="location" name="location" type="text" class="mt-1.5 block w-full"
                    :value="old('location', $event?->location)" placeholder="Ex. Casablanca, Maroc" required />
                <x-input-error :messages="$errors->get('location')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="event_date" value="Date et heure *" />
                <x-text-input id="event_date" name="event_date" type="datetime-local" class="mt-1.5 block w-full"
                    :value="old('event_date', $event?->event_date?->format('Y-m-d\TH:i'))" required />
                <x-input-error :messages="$errors->get('event_date')" class="mt-2" />
            </div>
        </div>
    </div>

    <!-- Type de placement -->
    @if ($seatedPlacementEnabled)
        <div>
            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">
                <x-icon name="users" class="w-4 h-4 text-brand" /> Type de placement
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label x-bind:class="placement === 'standing' ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-slate-200 hover:border-slate-300'"
                       class="flex flex-col gap-1.5 border-2 rounded-2xl px-4 py-4 cursor-pointer transition">
                    <input type="radio" name="placement_mode" value="standing" x-model="placement" class="sr-only">
                    <span class="text-sm font-bold" x-bind:class="placement === 'standing' ? 'text-brand-700' : 'text-slate-800'">Placement libre / Debout</span>
                    <span class="text-xs text-slate-500">Festivals, concerts — pas de numéro de siège</span>
                </label>
                <label x-bind:class="placement === 'seated' ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-slate-200 hover:border-slate-300'"
                       class="flex flex-col gap-1.5 border-2 rounded-2xl px-4 py-4 cursor-pointer transition">
                    <input type="radio" name="placement_mode" value="seated" x-model="placement" class="sr-only">
                    <span class="text-sm font-bold" x-bind:class="placement === 'seated' ? 'text-brand-700' : 'text-slate-800'">Places assises numérotées</span>
                    <span class="text-xs text-slate-500">Galas, conférences, théâtres — siège attribué à l'achat</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('placement_mode')" class="mt-2" />
        </div>
    @else
        <input type="hidden" name="placement_mode" value="{{ $currentPlacement }}">
        @unless ($event && $event->isSeatedPlacement())
            <p class="flex items-center gap-2 text-xs text-slate-500 rounded-xl border border-slate-100 bg-slate-50/80 px-3.5 py-2.5">
                <x-icon name="users" class="w-4 h-4 shrink-0 text-slate-400" />
                Placement libre — pas de numéro de siège assigné aux participants.
            </p>
        @endunless
    @endif

    <!-- Types de billets (prix saisis par l'organisateur) -->
    <div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 uppercase tracking-wide">
                <x-icon name="ticket" class="w-4 h-4 text-brand" /> Types de billets &amp; tarifs
            </h3>
            <button type="button" @click="addType()"
                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-brand-50 text-brand-700 hover:bg-brand-100 transition">
                <x-icon name="plus" class="w-3.5 h-3.5" /> Ajouter un pass
            </button>
        </div>
        <p class="text-xs text-slate-500 mb-4">
            Par défaut : <strong>Accès Général</strong>, <strong>0 {{ \App\Support\Money::symbol() }}</strong>, <strong>100</strong> places.
            Laissez le nom vide pour un libellé automatique (<em>Entrée Gratuite</em> si le prix est 0).
        </p>

        <div class="space-y-3">
            <template x-for="(type, index) in types" :key="index">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-4 rounded-2xl border border-slate-200 bg-slate-50/50">
                    <input type="hidden" :name="'ticket_types['+index+'][id]'" :value="type.id || ''">
                    <div class="sm:col-span-5">
                        <label class="text-xs font-semibold text-slate-600">Nom du pass</label>
                        <input type="text" :name="'ticket_types['+index+'][name]'" x-model="type.name"
                               placeholder="Ex. VIP, Accès Général…"
                               class="mt-1 block w-full border-slate-200 bg-white focus:border-brand focus:ring-brand/40 rounded-xl shadow-sm text-sm">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="text-xs font-semibold text-slate-600">Prix ({{ \App\Support\Money::symbol() }}) *</label>
                        <input type="number" step="0.01" min="0" :name="'ticket_types['+index+'][price]'" x-model="type.price" required
                               placeholder="0"
                               class="mt-1 block w-full border-slate-200 bg-white focus:border-brand focus:ring-brand/40 rounded-xl shadow-sm text-sm">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="text-xs font-semibold text-slate-600">Quantité *</label>
                        <input type="number" min="1" :name="'ticket_types['+index+'][quantity]'" x-model="type.quantity" required
                               class="mt-1 block w-full border-slate-200 bg-white focus:border-brand focus:ring-brand/40 rounded-xl shadow-sm text-sm">
                    </div>
                    <div class="sm:col-span-1 flex items-end">
                        <button type="button" @click="removeType(index)" x-show="types.length > 1"
                                class="w-full inline-flex items-center justify-center h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition"
                                title="Supprimer">
                            <x-icon name="x-mark" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <x-input-error :messages="$errors->get('ticket_types')" class="mt-2" />
        @if ($seatedPlacementEnabled)
            <p class="mt-3 text-xs font-medium" x-bind:class="placement === 'seated' ? 'text-brand' : 'text-slate-500'"
               x-text="placement === 'seated'
                  ? 'Mode assis : chaque billet vendu recevra automatiquement une rangée / un siège.'
                  : 'Mode debout : les billets afficheront « Placement libre ».'"></p>
        @else
            <p class="mt-3 text-xs text-slate-500">Les billets afficheront « Placement libre ».</p>
        @endif
    </div>

    <!-- Statut -->
    @if ($event)
        <div>
            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">
                <x-icon name="check-badge" class="w-4 h-4 text-brand" /> Statut de publication
            </h3>

            @php
                $statusOptions = ($event->is_paid || ! \App\Models\Event::requiresPublicationPayment())
                    ? ['published' => ['label' => 'Publié', 'icon' => 'check-circle'], 'draft' => ['label' => 'Brouillon', 'icon' => 'pencil-square'], 'cancelled' => ['label' => 'Annulé', 'icon' => 'x-circle']]
                    : ['draft' => ['label' => 'Brouillon', 'icon' => 'pencil-square'], 'cancelled' => ['label' => 'Annulé', 'icon' => 'x-circle']];
                $currentStatus = old('status', $event->status);
            @endphp

            <div class="grid grid-cols-2 gap-3">
                @foreach ($statusOptions as $value => $meta)
                    <label class="relative flex flex-col items-center gap-1.5 border-2 rounded-2xl px-3 py-3.5 cursor-pointer transition text-center
                        {{ $currentStatus === $value ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-slate-200 hover:border-slate-300' }}">
                        <input type="radio" name="status" value="{{ $value }}" class="sr-only" {{ $currentStatus === $value ? 'checked' : '' }}
                            onchange="this.closest('form').querySelectorAll('[name=status]').forEach(r => r.closest('label').classList.remove('border-brand','bg-brand-50','ring-2','ring-brand/20')); this.closest('label').classList.add('border-brand','bg-brand-50','ring-2','ring-brand/20');">
                        <x-icon :name="$meta['icon']" class="w-5 h-5 {{ $currentStatus === $value ? 'text-brand' : 'text-slate-400' }}" />
                        <span class="text-xs font-semibold {{ $currentStatus === $value ? 'text-brand-700' : 'text-slate-600' }}">{{ $meta['label'] }}</span>
                    </label>
                @endforeach
            </div>

            @if ($event->needsPayment())
                <p class="flex items-center gap-1.5 mt-3 text-xs text-amber-600 font-medium">
                    <x-icon name="banknotes" class="w-4 h-4 shrink-0" />
                    Cet événement n'est pas encore publié.
                    <a href="{{ route('organizer.events.pay', $event) }}" class="underline hover:text-amber-700">Régler les frais de publication</a>
                </p>
            @endif

            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    @else
        <div class="flex items-start gap-3 rounded-2xl border border-brand-100 bg-brand-50/60 px-4 py-3.5">
            <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-white text-brand shrink-0 shadow-sm">
                <x-icon name="check-circle" class="w-4.5 h-4.5" />
            </span>
            <p class="text-sm text-brand-800">
                @if (\App\Models\Event::requiresPublicationPayment())
                    Votre événement sera créé en <strong>brouillon</strong>. Vous serez redirigé vers le paiement des frais de publication
                    (<strong><x-money :amount="\App\Models\Event::publicationFee()" /></strong>) pour le rendre visible dans le catalogue public.
                @else
                    Votre événement sera <strong>publié immédiatement</strong> et visible dans le catalogue public dès sa création.
                @endif
            </p>
        </div>
    @endif

    <!-- Réception des paiements -->
    <div x-show="!isFreeEvent" x-cloak>
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 uppercase tracking-wide mb-2">
            <x-icon name="banknotes" class="w-4 h-4 text-brand" /> Moyens de paiement acceptés
        </h3>
        <p class="text-xs text-slate-500 mb-4">
            <strong class="text-slate-700">Obligatoire</strong> pour un événement payant — sélectionnez au moins un moyen de paiement et renseignez les coordonnées correspondantes.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            @foreach (\App\Models\Event::PARTICIPANT_PAYMENT_METHODS as $key => $label)
                <label class="flex flex-col items-center gap-1.5 border-2 rounded-2xl px-3 py-3.5 cursor-pointer transition text-center"
                       x-bind:class="toggles('{{ $key }}') ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-slate-200 hover:border-slate-300'">
                    <input type="checkbox" value="{{ $key }}"
                           x-bind:name="!isFreeEvent ? 'accepted_payment_methods[]' : null"
                           x-bind:checked="toggles('{{ $key }}')"
                           @change="toggle('{{ $key }}')"
                           class="sr-only">
                    @if ($key === 'mobile_money')
                        <x-icon name="device-phone-mobile" class="w-5 h-5" x-bind:class="toggles('{{ $key }}') ? 'text-brand' : 'text-slate-400'" />
                    @elseif ($key === 'card')
                        <x-icon name="credit-card" class="w-5 h-5" x-bind:class="toggles('{{ $key }}') ? 'text-brand' : 'text-slate-400'" />
                    @else
                        <x-icon name="banknotes" class="w-5 h-5" x-bind:class="toggles('{{ $key }}') ? 'text-brand' : 'text-slate-400'" />
                    @endif
                    <span class="text-xs font-semibold" x-bind:class="toggles('{{ $key }}') ? 'text-brand-700' : 'text-slate-600'">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('accepted_payment_methods')" class="mb-4" />

        {{-- Mobile Money --}}
        <div x-show="toggles('mobile_money')" x-cloak class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 mb-4 space-y-4">
            <h4 class="flex items-center gap-2 text-xs font-bold text-slate-800 uppercase tracking-wide">
                <x-icon name="device-phone-mobile" class="w-4 h-4 text-brand" /> Mobile Money
            </h4>
            <p class="text-xs text-slate-500 -mt-2">Les participants verront ce numéro pour vous envoyer le paiement.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="organizer_mobile_provider" value="Opérateur *" />
                    <select id="organizer_mobile_provider" name="organizer_mobile_provider" class="mt-1.5 ep-input">
                        <option value="">— Choisir —</option>
                        @foreach (\App\Models\Ticket::MOBILE_PROVIDERS as $key => $label)
                            <option value="{{ $key }}" @selected(old('organizer_mobile_provider', auth()->user()->mobile_money_provider) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('organizer_mobile_provider')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="organizer_phone" value="Numéro Mobile Money *" />
                    <x-text-input id="organizer_phone" name="organizer_phone" type="tel" class="mt-1.5 block w-full"
                        :value="old('organizer_phone', auth()->user()->phone)" placeholder="Ex. 06 12 34 56 78" />
                    <x-input-error :messages="$errors->get('organizer_phone')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Compte bancaire pour recevoir les paiements --}}
        <div x-show="toggles('card')" x-cloak class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 mb-4 space-y-4">
            <h4 class="flex items-center gap-2 text-xs font-bold text-slate-800 uppercase tracking-wide">
                <x-icon name="credit-card" class="w-4 h-4 text-brand" /> Carte bancaire / virement
            </h4>
            <p class="text-xs text-slate-500 -mt-2">
                Indiquez le compte sur lequel vous recevrez l'argent. Ce n'est pas un paiement par carte :
                <strong>pas besoin</strong> de numéro de carte, date d'expiration ni code CVC ici — ces champs apparaissent uniquement
                quand quelqu'un <em>paie</em> (participant à la réservation@if (\App\Models\Event::requiresPublicationPayment()), ou vous aux frais de publication@endif).
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="bank_account_holder" value="Titulaire du compte *" />
                    <x-text-input id="bank_account_holder" name="bank_account_holder" type="text" class="mt-1.5 block w-full"
                        :value="old('bank_account_holder', auth()->user()->bank_account_holder)" placeholder="Nom de l'organisateur ou de la société" />
                    <x-input-error :messages="$errors->get('bank_account_holder')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="bank_name" value="Banque *" />
                    <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1.5 block w-full"
                        :value="old('bank_name', auth()->user()->bank_name)" placeholder="Ex. Attijariwafa Bank" />
                    <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="bank_account_number" value="RIB / IBAN *" />
                    <x-text-input id="bank_account_number" name="bank_account_number" type="text" class="mt-1.5 block w-full font-mono"
                        :value="old('bank_account_number', auth()->user()->bank_account_number)" placeholder="Ex. MA64 1234 5678 9012 3456 7890 123" />
                    <x-input-error :messages="$errors->get('bank_account_number')" class="mt-2" />
                </div>
            </div>
        </div>

        <p x-show="toggles('cash')" x-cloak class="text-xs text-slate-500 rounded-xl border border-dashed border-slate-200 px-4 py-3">
            <x-icon name="banknotes" class="w-4 h-4 inline text-brand mr-1" />
            Espèces : les participants paieront sur place à l'entrée avec leur QR code.
        </p>
    </div>

    <p x-show="isFreeEvent" x-cloak class="flex items-center gap-2 text-xs text-slate-500 rounded-xl border border-emerald-100 bg-emerald-50/80 px-3.5 py-2.5">
        <x-icon name="check-circle" class="w-4 h-4 shrink-0 text-emerald-600" />
        Événement gratuit — aucun moyen de paiement requis.
    </p>

    <!-- Photo -->
    <div>
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">
            <x-icon name="photo" class="w-4 h-4 text-brand" /> Photo de l'événement
        </h3>

        @if ($event?->image_path)
            <div class="mb-3 flex items-center gap-3">
                <img src="{{ asset('storage/'.$event->image_path) }}" alt="" class="h-16 w-24 rounded-xl object-cover border border-slate-200">
                <p class="text-xs text-slate-400">Image actuelle — sélectionnez un fichier ci-dessous pour la remplacer.</p>
            </div>
        @endif

        <label for="image"
               class="flex flex-col items-center justify-center gap-2 w-full border-2 border-dashed border-slate-200 hover:border-brand hover:bg-brand-50/40 rounded-2xl py-8 cursor-pointer transition">
            <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-brand-100 text-brand">
                <x-icon name="photo" class="w-5 h-5" />
            </span>
            <span class="text-sm font-semibold text-slate-700">Cliquez pour choisir une image</span>
            <span class="text-xs text-slate-400">JPG, PNG — 4 Mo maximum</span>
            <input id="image" name="image" type="file" accept="image/*" class="sr-only"
                   onchange="this.nextElementSibling.textContent = this.files[0]?.name ?? ''">
            <span class="text-xs font-medium text-brand"></span>
        </label>
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>
</div>
