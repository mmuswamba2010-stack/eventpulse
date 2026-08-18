<x-app-layout>
    @php $remaining = $event->remainingSeats(); @endphp

    {{-- Hero --}}
    <div class="relative h-72 sm:h-96 bg-charcoal overflow-hidden">
        @if ($event->image_path)
            <img src="{{ asset('storage/'.$event->image_path) }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-charcoal">
                <img src="{{ asset('images/brand/mark.svg') }}" alt="" class="w-20 h-20 opacity-30">
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-charcoal via-charcoal/50 to-transparent"></div>

        <div class="absolute inset-0 flex flex-col justify-between max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <a href="{{ route('events.index') }}"
               class="inline-flex items-center gap-1.5 self-start px-3.5 py-2 rounded-lg bg-white/10 backdrop-blur text-white text-sm font-medium hover:bg-white/20 transition">
                <x-icon name="arrow-left" class="w-4 h-4" /> Catalogue
            </a>

            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <x-category-badge :category="$event->category ?? 'other'" />
                    @if ($event->status === 'cancelled')
                        <span class="inline-flex px-2.5 py-1 rounded-md bg-coral text-white text-xs font-bold uppercase">Annulé</span>
                    @endif
                </div>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-white tracking-tight max-w-3xl">{{ $event->title }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-frost">
                    <span class="flex items-center gap-1.5"><x-icon name="calendar" class="w-4 h-4" /> {{ $event->event_date->translatedFormat('l d F Y à H:i') }}</span>
                    <span class="flex items-center gap-1.5"><x-icon name="map-pin" class="w-4 h-4" /> {{ $event->location }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="py-10 pb-16">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 px-4 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            {{-- Contenu --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="ep-card p-6 sm:p-8">
                    <h2 class="font-display font-bold text-lg text-charcoal mb-4">À propos</h2>
                    <div class="prose prose-slate max-w-none text-frost whitespace-pre-line leading-relaxed">{{ $event->description }}</div>
                </div>

                <div class="ep-card p-6 sm:p-8 flex items-center gap-4">
                    <span class="flex items-center justify-center w-11 h-11 rounded-lg bg-charcoal text-white font-bold text-base shrink-0">
                        {{ Str::of($event->user->name)->substr(0, 1) }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-frost font-medium uppercase tracking-wide">Organisé par</p>
                        <p class="font-display font-bold text-charcoal">{{ $event->user->name }}</p>
                        @if ($event->user->phone && $event->user->mobile_money_provider && $event->acceptsPaymentMethod('mobile_money'))
                            <p class="mt-1 text-xs text-frost">
                                Mobile Money · {{ $event->user->mobileMoneyProviderLabel() }} · {{ $event->user->phone }}
                            </p>
                        @endif
                        @if ($event->user->bank_account_number && $event->acceptsPaymentMethod('card'))
                            <p class="mt-1 text-xs text-frost">
                                Carte / virement · {{ $event->user->bank_name }} · {{ $event->user->bank_account_number }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Réservation --}}
            <div class="lg:col-span-1">
                @php
                    $types = $event->ticketTypes;
                    $minPrice = $types->isNotEmpty() ? (float) $types->min('price') : (float) $event->price;
                    $placementLabel = $event->isSeatedPlacement() ? 'Places assises numérotées' : 'Placement libre / Debout';
                    $showPlacementBadge = \App\Models\Event::allowsSeatedPlacement() || $event->isSeatedPlacement();
                    $isFreeEvent = $event->isFreeEvent();
                @endphp
                <div class="ep-card border-l-4 border-l-coral p-5 sm:p-6 space-y-4"
                     x-data="{
                        selected: @js(old('ticket_type_id', $types->firstWhere(fn ($t) => $t->remainingSeats() > 0)?->id ?? $types->first()?->id)),
                        types: @js($types->map(fn ($t) => [
                            'id' => $t->id,
                            'name' => $t->name,
                            'price' => (float) $t->price,
                            'remaining' => $t->remainingSeats(),
                        ])->values()),
                        payMethod: @js(old('payment_method', $event->acceptedPaymentMethods()[0] ?? 'mobile_money')),
                        get current() { return this.types.find(t => t.id == this.selected) || this.types[0]; },
                        get requiresPayment() {
                            return this.current && parseFloat(this.current.price) > 0;
                        }
                     }">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-frost">À partir de</p>
                        <p class="font-display text-2xl font-bold text-charcoal">
                            <x-money :amount="$minPrice" />
                        </p>
                        @if ($showPlacementBadge)
                            <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-frost">
                                <x-icon name="users" class="w-3.5 h-3.5" /> {{ $placementLabel }}
                            </p>
                        @endif
                    </div>

                    <div class="text-sm border-t border-charcoal/[0.06] pt-3">
                        <p class="flex items-center gap-2 text-frost">
                            <x-icon name="users" class="w-4 h-4 shrink-0 text-coral" />
                            {{ $remaining }} / {{ $event->capacity }} places disponibles
                        </p>
                        <div class="mt-2 h-1.5 rounded-full bg-charcoal/[0.06] overflow-hidden">
                            @php $filledPct = $event->capacity > 0 ? min(100, round((($event->capacity - $remaining) / $event->capacity) * 100)) : 0; @endphp
                            <div class="h-full rounded-full bg-coral" style="width: {{ $filledPct }}%"></div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-charcoal/[0.06]">
                        @if ($event->status === 'cancelled')
                            <div class="flex items-center justify-center gap-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 p-4 text-sm font-semibold">
                                <x-icon name="x-circle" class="w-5 h-5 shrink-0" /> Événement annulé
                            </div>
                        @elseif (! $event->isUpcoming())
                            <div class="rounded-xl bg-slate-100 text-slate-500 p-4 text-sm text-center font-medium">
                                Cet événement est terminé.
                            </div>
                        @elseif ($remaining <= 0)
                            <div class="flex items-center justify-center gap-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 p-4 text-sm font-bold">
                                Complet !
                            </div>
                        @elseif ($types->isEmpty())
                            <div class="rounded-xl bg-amber-50 border border-amber-100 text-amber-800 p-4 text-sm text-center">
                                Aucun type de billet n'est encore configuré pour cet événement.
                            </div>
                        @elseif (auth()->check() && auth()->id() === $event->user_id)
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-center space-y-3">
                                <p class="text-sm text-slate-600">C'est votre événement — gérez-le depuis votre espace organisateur.</p>
                                <a href="{{ route('organizer.events.index') }}"
                                   class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-ink text-white font-semibold px-4 py-3 text-sm hover:bg-slate-800 transition">
                                    Gérer mes événements
                                </a>
                            </div>
                        @else
                            @auth
                                @if ($alreadyBooked)
                                    <div class="flex items-center gap-2 rounded-xl bg-sky-50 border border-sky-200 text-sky-700 p-3 text-sm mb-3">
                                        <x-icon name="ticket" class="w-4 h-4 shrink-0" />
                                        <span>
                                            @if ($isFreeEvent)
                                                Vous avez déjà réservé votre place.
                                            @else
                                                Vous avez déjà des billets.
                                            @endif
                                            <a href="{{ route('tickets.index') }}" class="underline font-semibold">Voir mes billets</a>
                                        </span>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('tickets.store', $event) }}" class="space-y-3">
                                    @csrf

                                    <div>
                                        <x-input-label value="Type de billet" class="text-xs" />
                                        <div class="mt-1 space-y-1.5">
                                            @foreach ($types as $type)
                                                @php $typeRemaining = $type->remainingSeats(); @endphp
                                                <label class="flex items-center justify-between gap-2 border rounded-lg px-3 py-2 cursor-pointer transition text-sm
                                                    {{ $typeRemaining <= 0 ? 'opacity-50 border-charcoal/[0.06]' : '' }}"
                                                       x-bind:class="selected == {{ $type->id }} ? 'border-coral bg-coral-muted/40' : 'border-charcoal/[0.08] hover:border-charcoal/20'">
                                                    <span class="flex items-center gap-2 min-w-0">
                                                        <input type="radio" name="ticket_type_id" value="{{ $type->id }}"
                                                               x-model.number="selected"
                                                               {{ $typeRemaining <= 0 ? 'disabled' : '' }}
                                                               class="text-coral focus:ring-coral">
                                                        <span class="font-medium text-charcoal truncate">{{ $type->name }}</span>
                                                    </span>
                                                    <span class="font-semibold text-charcoal shrink-0">
                                                        <x-money :amount="$type->price" />
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <x-input-error :messages="$errors->get('ticket_type_id')" class="mt-1" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label for="quantity" value="Quantité" class="text-xs" />
                                            <select id="quantity" name="quantity" class="mt-1 block w-full ep-input py-2 text-sm">
                                                @for ($i = 1; $i <= 10; $i++)
                                                    <option value="{{ $i }}" @selected(old('quantity') == $i)>{{ $i }}</option>
                                                @endfor
                                            </select>
                                            <x-input-error :messages="$errors->get('quantity')" class="mt-1" />
                                        </div>
                                    </div>

                                    @php
                                        $acceptedPayments = $event->acceptedPaymentMethods();
                                        $organizer = $event->user;
                                    @endphp
                                    <div x-show="requiresPayment" x-cloak>
                                        <x-input-label value="Paiement" class="text-xs" />
                                        <div class="mt-1 grid grid-cols-3 gap-1.5">
                                            @foreach ($acceptedPayments as $method)
                                                <label class="flex flex-col items-center gap-1 border rounded-lg px-1.5 py-2 cursor-pointer transition text-center"
                                                       x-bind:class="payMethod === '{{ $method }}' ? 'border-coral bg-coral-muted/40' : 'border-charcoal/[0.08]'">
                                                    <input type="radio" name="payment_method" value="{{ $method }}"
                                                           x-model="payMethod" class="sr-only">
                                                    @if ($method === 'mobile_money')
                                                        <x-icon name="device-phone-mobile" class="w-4 h-4 text-coral" />
                                                        <span class="text-[10px] font-semibold text-charcoal leading-tight">Mobile</span>
                                                    @elseif ($method === 'card')
                                                        <x-icon name="credit-card" class="w-4 h-4 text-coral" />
                                                        <span class="text-[10px] font-semibold text-charcoal leading-tight">Carte</span>
                                                    @else
                                                        <x-icon name="banknotes" class="w-4 h-4 text-coral" />
                                                        <span class="text-[10px] font-semibold text-charcoal leading-tight">Espèces</span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                        <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />

                                    <div x-show="payMethod === 'mobile_money' && requiresPayment" x-cloak class="rounded-xl border border-coral/20 bg-coral-muted/30 p-3 space-y-2.5 mt-3">
                                        @if ($organizer->phone)
                                            <div class="rounded-lg bg-white px-3 py-2.5 text-sm">
                                                <p class="text-[10px] font-bold uppercase tracking-wide text-coral">Envoyez le paiement à</p>
                                                <p class="font-semibold text-charcoal">{{ $organizer->name }}</p>
                                                <p class="mt-0.5 font-mono text-sm text-charcoal">
                                                    @if ($organizer->mobileMoneyProviderLabel())
                                                        {{ $organizer->mobileMoneyProviderLabel() }} ·
                                                    @endif
                                                    {{ $organizer->phone }}
                                                </p>
                                            </div>
                                        @else
                                            <p class="text-xs text-frost">L'organisateur n'a pas encore renseigné son numéro Mobile Money.</p>
                                        @endif

                                        <div>
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-frost mb-1">Votre opérateur</p>
                                            <div class="grid grid-cols-3 gap-1.5" data-mobile-providers>
                                                <x-mobile-money-provider compact value="mpesa" label="M-Pesa" logo="mpesa.png" :selected="old('mobile_provider') === 'mpesa'" />
                                                <x-mobile-money-provider compact value="orange_money" label="Orange Money" logo="orange-money.svg" :selected="old('mobile_provider', 'orange_money') === 'orange_money'" />
                                                <x-mobile-money-provider compact value="airtel_money" label="Airtel Money" logo="airtel-money.svg" :selected="old('mobile_provider') === 'airtel_money'" />
                                            </div>
                                            <x-input-error :messages="$errors->get('mobile_provider')" class="mt-1" />
                                        </div>

                                        <div>
                                            <x-input-label for="phone_number" value="Votre numéro (confirmation)" class="text-xs" />
                                            <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full py-2 text-sm"
                                                :value="old('phone_number', auth()->user()?->phone)" placeholder="06 12 34 56 78" />
                                            <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
                                        </div>
                                    </div>

                                    <div x-show="payMethod === 'card' && requiresPayment" x-cloak class="rounded-xl border border-charcoal/[0.08] bg-white p-3 space-y-2.5 mt-3">
                                        @if ($organizer->bank_account_number)
                                            <div class="rounded-lg bg-cream px-3 py-2.5 text-sm">
                                                <p class="text-[10px] font-bold uppercase tracking-wide text-coral">Coordonnées bancaires de l'organisateur</p>
                                                <p class="font-semibold text-charcoal">{{ $organizer->bank_account_holder ?: $organizer->name }}</p>
                                                <p class="mt-0.5 text-xs text-frost">{{ $organizer->bank_name }}</p>
                                                <p class="mt-0.5 font-mono text-sm text-charcoal">{{ $organizer->bank_account_number }}</p>
                                            </div>
                                        @else
                                            <p class="text-xs text-frost">L'organisateur n'a pas encore renseigné ses coordonnées bancaires.</p>
                                        @endif

                                        <div>
                                            <x-input-label for="card_name" value="Titulaire" class="text-xs" />
                                            <x-text-input id="card_name" name="card_name" type="text" class="mt-1 block w-full py-2 text-sm"
                                                :value="old('card_name')" placeholder="Nom sur la carte" />
                                            <x-input-error :messages="$errors->get('card_name')" class="mt-1" />
                                        </div>
                                        <div>
                                            <x-input-label for="card_number" value="Numéro de carte" class="text-xs" />
                                            <x-text-input id="card_number" name="card_number" type="text" class="mt-1 block w-full py-2 text-sm"
                                                value="" placeholder="4242 4242 4242 4242" maxlength="23" autocomplete="off" />
                                            <x-input-error :messages="$errors->get('card_number')" class="mt-1" />
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <x-input-label for="card_expiry" value="Date d'expiration" class="text-xs" />
                                                <x-text-input id="card_expiry" name="card_expiry" type="text" class="mt-1 block w-full py-2 text-sm"
                                                    :value="old('card_expiry')" placeholder="MM/AA" maxlength="7" />
                                            </div>
                                            <div>
                                                <x-input-label for="card_cvc" value="Code CVC (3 chiffres)" class="text-xs" />
                                                <x-text-input id="card_cvc" name="card_cvc" type="text" class="mt-1 block w-full py-2 text-sm"
                                                    value="" placeholder="123" maxlength="4" autocomplete="off" />
                                            </div>
                                        </div>
                                    </div>

                                    <p x-show="payMethod === 'cash' && requiresPayment" x-cloak class="text-xs text-frost bg-cream rounded-lg px-3 py-2 mt-3">
                                        Règlement en espèces à l'entrée avec votre QR code.
                                    </p>
                                    </div>

                                    <p x-show="!requiresPayment" x-cloak class="text-xs text-frost bg-cream rounded-lg px-3 py-2">
                                        Entrée gratuite — aucun paiement requis.
                                    </p>

                                    <button type="submit" class="ep-btn w-full justify-center py-3">
                                        <x-icon name="ticket" class="w-4 h-4" />
                                        <span x-text="requiresPayment ? 'Réserver mon billet' : 'Réserver ma place'">Réserver</span>
                                    </button>
                                    <p x-show="requiresPayment" x-cloak class="text-[10px] text-center text-frost">Paiement simulé — aucun prélèvement réel.</p>
                                </form>
                            @else
                                <a href="{{ route('login') }}"
                                   class="flex items-center justify-center gap-2 w-full bg-brand hover:bg-brand-700 text-white font-semibold rounded-xl px-4 py-3.5 text-sm transition">
                                    Connectez-vous pour réserver
                                </a>
                                <p class="mt-2 text-[11px] text-center text-slate-400">
                                    Pas encore de compte ?
                                    <a href="{{ route('register') }}" class="text-brand font-semibold hover:underline">Créer un compte participant</a>
                                </p>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
