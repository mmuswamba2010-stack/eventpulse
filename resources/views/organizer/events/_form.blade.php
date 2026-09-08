@php
    /** @var \App\Models\Event|null $event */
    $event = $event ?? null;
    $defaultPassName = __('General admission');
    $oldTypes = old('ticket_types');
    if ($oldTypes === null && $event) {
        $oldTypes = $event->ticketTypes->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'price' => (string) $t->price,
            'quantity' => $t->quantity,
            'is_active' => $t->is_active,
            'sale_starts_at' => $t->sale_starts_at?->format('Y-m-d\TH:i') ?? '',
            'sale_ends_at' => $t->sale_ends_at?->format('Y-m-d\TH:i') ?? '',
            'sold' => $t->soldCount(),
        ])->values()->all();

        if (\App\Support\Money::usdEnabled() && \App\Support\Money::cdfPerUsd() > 0) {
            $oldTypes = array_map(function ($row) {
                $row['price'] = (string) \App\Support\Money::cdfToUsd((float) ($row['price'] ?? 0));

                return $row;
            }, $oldTypes);
        }
    }
    if (empty($oldTypes)) {
        $oldTypes = [['id' => null, 'name' => 'Standard', 'price' => '0', 'quantity' => 100, 'is_active' => true, 'sale_starts_at' => '', 'sale_ends_at' => '', 'sold' => 0]];
    }
    $currentPlacement = old('placement_mode', $event?->placement_mode ?? 'standing');
    if (! \App\Models\Event::allowsSeatedPlacement()) {
        $currentPlacement = ($event && $event->isSeatedPlacement())
            ? \App\Models\Event::PLACEMENT_SEATED
            : \App\Models\Event::PLACEMENT_STANDING;
    }
    $seatedPlacementEnabled = \App\Models\Event::allowsSeatedPlacement();
    $defaultMethods = old('accepted_payment_methods', $event?->accepted_payment_methods ?? ['card', 'cash']);
    if (! is_array($defaultMethods)) {
        $defaultMethods = ['card', 'cash'];
    }
    if ($event && ! $event->isFreeEvent() && $defaultMethods === []) {
        $defaultMethods = ['card', 'cash'];
    }
@endphp

<div class="space-y-8"
     x-data="{
        placement: @js($currentPlacement),
        types: @js($oldTypes),
        methods: @js(array_values($defaultMethods)),
        defaultPassName: @js($defaultPassName),
        seatedHint: @js(__('Seated mode ticket hint')),
        standingHint: @js(__('Standing mode ticket hint')),
        cdfPerUsd: @js(\App\Support\Money::usdEnabled() ? \App\Support\Money::cdfPerUsd() : 0),
        fcSymbol: @js(\App\Support\Money::symbol()),
        formatFc(usd) {
            const value = Math.round(parseFloat(usd || 0) * this.cdfPerUsd);
            if (value <= 0) return '';
            return '≈ ' + value.toLocaleString('fr-FR') + ' ' + this.fcSymbol;
        },
        addType() {
            this.types.push({ id: null, name: '', price: '0', quantity: 100, is_active: true, sale_starts_at: '', sale_ends_at: '', sold: 0 });
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
                this.methods = ['card', 'cash'];
            }
        },
        get isFreeEvent() {
            return this.types.length > 0 && this.types.every(t => parseFloat(t.price || 0) <= 0);
        }
     }"
     x-init="$watch('isFreeEvent', () => ensurePaidMethods())">
    <div>
        <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide mb-4">
            <x-icon name="sparkles" class="w-4 h-4 text-brand" /> {{ __('General information') }}
        </h3>
        <div class="grid grid-cols-1 gap-5">
            <div>
                <x-input-label for="title" :value="__('Event title label')" />
                <x-text-input id="title" name="title" type="text" class="mt-1.5 block w-full"
                    :value="old('title', $event?->title)" :placeholder="__('Event title placeholder')" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="category" :value="__('Category label')" />
                <select id="category" name="category" required
                    class="mt-1.5 ep-input">
                    @foreach (\App\Models\Event::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $event?->category ?? 'other') === $key)>{{ \App\Models\Event::categoryLabelFor($key) }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" :value="__('Description label')" />
                <textarea id="description" name="description" rows="5" required placeholder="{{ __('Description placeholder') }}"
                    class="mt-1.5 block w-full border-charcoal/10 dark:border-white/10 bg-charcoal/[0.03] dark:bg-white/5 focus:bg-white focus:border-brand focus:ring-brand/40 rounded-xl shadow-sm text-sm placeholder:text-frost transition">{{ old('description', $event?->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
    </div>

    <div>
        <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide mb-4">
            <x-icon name="map-pin" class="w-4 h-4 text-brand" /> {{ __('Location and schedule') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <x-input-label for="location" :value="__('Location label')" />
                <x-text-input id="location" name="location" type="text" class="mt-1.5 block w-full"
                    :value="old('location', $event?->location)" :placeholder="__('Location placeholder')" required />
                <x-input-error :messages="$errors->get('location')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="event_date" :value="__('Date and time label')" />
                <x-text-input id="event_date" name="event_date" type="datetime-local" class="mt-1.5 block w-full"
                    :value="old('event_date', $event?->event_date?->format('Y-m-d\TH:i'))" required />
                <x-input-error :messages="$errors->get('event_date')" class="mt-2" />
            </div>
        </div>
    </div>

    @if ($seatedPlacementEnabled)
        <div>
            <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide mb-4">
                <x-icon name="users" class="w-4 h-4 text-brand" /> {{ __('Placement type') }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label x-bind:class="placement === 'standing' ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-charcoal/10 dark:border-white/10 hover:border-charcoal/20 dark:hover:border-white/20'"
                       class="flex flex-col gap-1.5 border-2 rounded-2xl px-4 py-4 cursor-pointer transition">
                    <input type="radio" name="placement_mode" value="standing" x-model="placement" class="sr-only">
                    <span class="text-sm font-bold" x-bind:class="placement === 'standing' ? 'text-brand-700' : 'text-charcoal dark:text-[#FAFAFA]'">{{ __('Standing placement') }}</span>
                    <span class="text-xs text-frost">{{ __('Standing placement hint') }}</span>
                </label>
                <label x-bind:class="placement === 'seated' ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-charcoal/10 dark:border-white/10 hover:border-charcoal/20 dark:hover:border-white/20'"
                       class="flex flex-col gap-1.5 border-2 rounded-2xl px-4 py-4 cursor-pointer transition">
                    <input type="radio" name="placement_mode" value="seated" x-model="placement" class="sr-only">
                    <span class="text-sm font-bold" x-bind:class="placement === 'seated' ? 'text-brand-700' : 'text-charcoal dark:text-[#FAFAFA]'">{{ __('Seated placement') }}</span>
                    <span class="text-xs text-frost">{{ __('Seated placement hint') }}</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('placement_mode')" class="mt-2" />
        </div>
    @else
        <input type="hidden" name="placement_mode" value="{{ $currentPlacement }}">
        @unless ($event && $event->isSeatedPlacement())
            <p class="flex items-center gap-2 text-xs text-frost rounded-xl border border-charcoal/5 dark:border-white/5 bg-charcoal/[0.03] dark:bg-white/5 px-3.5 py-2.5">
                <x-icon name="users" class="w-4 h-4 shrink-0 text-frost" />
                {{ __('Standing only notice') }}
            </p>
        @endunless
    @endif

    <div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide">
                <x-icon name="ticket" class="w-4 h-4 text-brand" /> {{ __('Ticket types section') }}
            </h3>
            <button type="button" @click="addType()"
                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-brand-50 text-brand-700 hover:bg-brand-100 transition">
                <x-icon name="plus" class="w-3.5 h-3.5" /> {{ __('Add pass') }}
            </button>
        </div>
        <p class="text-xs text-frost mb-4">
            {{ __('Ticket types tiers hint') }}
        </p>

        <div class="space-y-4">
            <template x-for="(type, index) in types" :key="index">
                <div class="rounded-2xl border border-charcoal/10 dark:border-white/10 bg-charcoal/[0.03] dark:bg-white/5 overflow-hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-4">
                        <input type="hidden" :name="'ticket_types['+index+'][id]'" :value="type.id || ''">
                        <input type="hidden" :name="'ticket_types['+index+'][is_active]'" :value="type.is_active ? '1' : '0'">
                        <div class="sm:col-span-4">
                            <label class="text-xs font-semibold text-frost">{{ __('Pass name') }}</label>
                            <input type="text" :name="'ticket_types['+index+'][name]'" x-model="type.name"
                                   placeholder="{{ __('Pass name examples placeholder') }}"
                                   class="mt-1 block w-full ep-input rounded-xl shadow-sm text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-frost">{{ __('Price label usd') }}</label>
                            <input type="number" step="0.01" min="0" :name="'ticket_types['+index+'][price]'" x-model="type.price" required
                                   placeholder="20"
                                   class="mt-1 block w-full ep-input rounded-xl shadow-sm text-sm">
                            <p x-show="cdfPerUsd > 0 && parseFloat(type.price) > 0" class="text-[10px] text-frost mt-0.5" x-text="formatFc(type.price)"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-frost">{{ __('Quantity label') }}</label>
                            <input type="number" min="1" :name="'ticket_types['+index+'][quantity]'" x-model="type.quantity" required
                                   class="mt-1 block w-full ep-input rounded-xl shadow-sm text-sm">
                            <p x-show="type.sold > 0" class="text-[10px] text-frost mt-0.5" x-text="'{{ __('Tickets sold short') }}'.replace(':count', type.sold)"></p>
                        </div>
                        <div class="sm:col-span-3 flex items-end">
                            <label class="inline-flex items-center gap-2 cursor-pointer rounded-xl border px-3 py-2.5 w-full transition"
                                   x-bind:class="type.is_active ? 'border-emerald-200 bg-emerald-50/80 text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300' : 'border-charcoal/10 text-frost'">
                                <input type="checkbox" x-model="type.is_active" class="rounded border-charcoal/20 text-brand focus:ring-brand/30">
                                <span class="text-xs font-semibold">{{ __('Ticket sale active toggle') }}</span>
                            </label>
                        </div>
                        <div class="sm:col-span-1 flex items-end">
                            <button type="button" @click="removeType(index)" x-show="types.length > 1"
                                    class="w-full inline-flex items-center justify-center h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition"
                                    title="{{ __('Delete') }}">
                                <x-icon name="x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 px-4 pb-4 pt-0 border-t border-charcoal/[0.06] dark:border-white/10">
                        <div>
                            <label class="text-xs font-semibold text-frost">{{ __('Ticket sale starts at') }}</label>
                            <input type="datetime-local" :name="'ticket_types['+index+'][sale_starts_at]'" x-model="type.sale_starts_at"
                                   class="mt-1 block w-full ep-input rounded-xl shadow-sm text-sm">
                            <p class="text-[10px] text-frost mt-0.5">{{ __('Ticket sale starts hint') }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-frost">{{ __('Ticket sale ends at') }}</label>
                            <input type="datetime-local" :name="'ticket_types['+index+'][sale_ends_at]'" x-model="type.sale_ends_at"
                                   class="mt-1 block w-full ep-input rounded-xl shadow-sm text-sm">
                            <p class="text-[10px] text-frost mt-0.5">{{ __('Ticket sale ends hint') }}</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <x-input-error :messages="$errors->get('ticket_types')" class="mt-2" />
        @if ($seatedPlacementEnabled)
            <p class="mt-3 text-xs font-medium" x-bind:class="placement === 'seated' ? 'text-brand' : 'text-frost'"
               x-text="placement === 'seated' ? seatedHint : standingHint"></p>
        @else
            <p class="mt-3 text-xs text-frost">{{ __('Standing tickets hint') }}</p>
        @endif
    </div>

    @if ($event)
        <div>
            <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide mb-4">
                <x-icon name="check-badge" class="w-4 h-4 text-brand" /> {{ __('Publication status') }}
            </h3>

            @php
                $statusOptions = ($event->is_paid || ! \App\Models\Event::requiresPublicationPayment())
                    ? ['published' => ['label' => __('Published'), 'icon' => 'check-circle'], 'draft' => ['label' => __('Draft'), 'icon' => 'pencil-square'], 'cancelled' => ['label' => __('Cancelled'), 'icon' => 'x-circle']]
                    : ['draft' => ['label' => __('Draft'), 'icon' => 'pencil-square'], 'cancelled' => ['label' => __('Cancelled'), 'icon' => 'x-circle']];
                $currentStatus = old('status', $event->status);
            @endphp

            <div class="grid grid-cols-2 gap-3">
                @foreach ($statusOptions as $value => $meta)
                    <label class="relative flex flex-col items-center gap-1.5 border-2 rounded-2xl px-3 py-3.5 cursor-pointer transition text-center
                        {{ $currentStatus === $value ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-charcoal/10 dark:border-white/10 hover:border-charcoal/20 dark:hover:border-white/20' }}">
                        <input type="radio" name="status" value="{{ $value }}" class="sr-only" {{ $currentStatus === $value ? 'checked' : '' }}
                            onchange="this.closest('form').querySelectorAll('[name=status]').forEach(r => r.closest('label').classList.remove('border-brand','bg-brand-50','ring-2','ring-brand/20')); this.closest('label').classList.add('border-brand','bg-brand-50','ring-2','ring-brand/20');">
                        <x-icon :name="$meta['icon']" class="w-5 h-5 {{ $currentStatus === $value ? 'text-brand' : 'text-frost' }}" />
                        <span class="text-xs font-semibold {{ $currentStatus === $value ? 'text-brand-700' : 'text-frost' }}">{{ $meta['label'] }}</span>
                    </label>
                @endforeach
            </div>

            @if ($event->needsPayment())
                <p class="flex items-center gap-1.5 mt-3 text-xs text-amber-600 font-medium">
                    <x-icon name="banknotes" class="w-4 h-4 shrink-0" />
                    {{ __('Event not published yet') }}
                    <a href="{{ route('organizer.events.pay', $event) }}" class="underline hover:text-amber-700">{{ __('Pay publication fees') }}</a>
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
                    {{ __('Event create draft before draft') }}
                    <strong>{{ __('Draft') }}</strong>.
                    {{ __('Event create draft before fee') }}
                    (<strong><x-money :amount="\App\Models\Event::publicationFee()" primary="usd" :free="false" /></strong>)
                    {{ __('Event create draft after fee') }}
                @else
                    {{ __('Event create publish before') }}
                    <strong>{{ __('Published immediately') }}</strong>
                    {{ __('Event create publish after') }}
                @endif
            </p>
        </div>
    @endif

    <div x-show="!isFreeEvent" x-cloak>
        <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide mb-2">
            <x-icon name="banknotes" class="w-4 h-4 text-brand" /> {{ __('Accepted payment methods') }}
        </h3>
        <p class="text-xs text-frost mb-4">
            {{ __('Paid event payment required') }}
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            @foreach (\App\Models\Event::PARTICIPANT_PAYMENT_METHODS as $key => $label)
                <label class="flex flex-col items-center gap-1.5 border-2 rounded-2xl px-3 py-3.5 cursor-pointer transition text-center"
                       x-bind:class="toggles('{{ $key }}') ? 'border-brand bg-brand-50 ring-2 ring-brand/20' : 'border-charcoal/10 dark:border-white/10 hover:border-charcoal/20 dark:hover:border-white/20'">
                    <input type="checkbox" value="{{ $key }}"
                           x-bind:name="!isFreeEvent ? 'accepted_payment_methods[]' : null"
                           x-bind:checked="toggles('{{ $key }}')"
                           @change="toggle('{{ $key }}')"
                           class="sr-only">
                    @if ($key === 'card')
                        <x-icon name="credit-card" class="w-5 h-5" x-bind:class="toggles('{{ $key }}') ? 'text-brand' : 'text-frost'" />
                    @else
                        <x-icon name="banknotes" class="w-5 h-5" x-bind:class="toggles('{{ $key }}') ? 'text-brand' : 'text-frost'" />
                    @endif
                    <span class="text-xs font-semibold" x-bind:class="toggles('{{ $key }}') ? 'text-brand-700' : 'text-frost'">{{ \App\Models\Event::paymentMethodLabelFor($key) }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('accepted_payment_methods')" class="mb-4" />

        <div x-show="toggles('card')" x-cloak class="rounded-2xl border border-charcoal/10 dark:border-white/10 bg-charcoal/[0.03] dark:bg-white/5 p-4 mb-4 space-y-4">
            <h4 class="flex items-center gap-2 text-xs font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide">
                <x-icon name="credit-card" class="w-4 h-4 text-brand" /> {{ __('Bank card section') }}
            </h4>
            <p class="text-xs text-frost -mt-2">
                {{ __('Bank account hint') }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="bank_account_holder" :value="__('Account holder label')" />
                    <x-text-input id="bank_account_holder" name="bank_account_holder" type="text" class="mt-1.5 block w-full"
                        :value="old('bank_account_holder', auth()->user()->bank_account_holder)" :placeholder="__('Account holder placeholder')" />
                    <x-input-error :messages="$errors->get('bank_account_holder')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="bank_name" :value="__('Bank label')" />
                    <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1.5 block w-full"
                        :value="old('bank_name', auth()->user()->bank_name)" :placeholder="__('Bank placeholder')" />
                    <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="bank_account_number" :value="__('RIB IBAN label')" />
                    <x-text-input id="bank_account_number" name="bank_account_number" type="text" class="mt-1.5 block w-full font-mono"
                        :value="old('bank_account_number', auth()->user()->bank_account_number)" :placeholder="__('RIB placeholder')" />
                    <x-input-error :messages="$errors->get('bank_account_number')" class="mt-2" />
                </div>
            </div>
        </div>

        <p x-show="toggles('cash')" x-cloak class="text-xs text-frost rounded-xl border border-dashed border-charcoal/10 dark:border-white/10 px-4 py-3">
            <x-icon name="banknotes" class="w-4 h-4 inline text-brand mr-1" />
            {{ __('Cash payment hint') }}
        </p>
    </div>

    <p x-show="isFreeEvent" x-cloak class="flex items-center gap-2 text-xs text-frost rounded-xl border border-emerald-100 bg-emerald-50/80 px-3.5 py-2.5">
        <x-icon name="check-circle" class="w-4 h-4 shrink-0 text-emerald-600" />
        {{ __('Free event notice') }}
    </p>

    <div>
        <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide mb-4">
            <x-icon name="photo" class="w-4 h-4 text-brand" /> {{ __('Event photo section') }}
        </h3>

        @if ($event?->image_path)
            <div class="mb-3 flex items-center gap-3">
                <x-event-image :event="$event" alt="" variant="thumb"
                               class="h-16 w-24 rounded-xl object-cover border border-charcoal/10 dark:border-white/10"
                               width="96" height="64" />
                <p class="text-xs text-frost">{{ __('Current image hint') }}</p>
            </div>
        @endif

        <label for="image"
               class="flex flex-col items-center justify-center gap-2 w-full border-2 border-dashed border-charcoal/10 dark:border-white/10 hover:border-brand hover:bg-brand-50/40 rounded-2xl py-8 cursor-pointer transition">
            <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-brand-100 text-brand">
                <x-icon name="photo" class="w-5 h-5" />
            </span>
            <span class="text-sm font-semibold text-charcoal dark:text-[#FAFAFA]">{{ __('Click choose image') }}</span>
            <span class="text-xs text-frost">{{ __('Image format hint') }}</span>
            <input id="image" name="image" type="file" accept="image/*" class="sr-only"
                   onchange="this.nextElementSibling.textContent = this.files[0]?.name ?? ''">
            <span class="text-xs font-medium text-brand"></span>
        </label>
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>
</div>
