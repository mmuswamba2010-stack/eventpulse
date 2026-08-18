<x-app-layout>
    <div class="relative overflow-hidden bg-ink ep-hero">

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <a href="{{ route('organizer.events.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-300 hover:text-white transition mb-8">
                <x-icon name="arrow-left" class="w-4 h-4" /> Retour à mes événements
            </a>

            <div class="text-center mb-10">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/10 text-xs font-semibold text-brand-200 mb-4">
                    <x-icon name="shield-check" class="w-3.5 h-3.5" /> Paiement sécurisé (simulation)
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    Publiez <span class="text-brand-200">{{ $event->title }}</span>
                </h1>
                <p class="mt-3 text-slate-300 max-w-xl mx-auto">
                    Réglez les frais de publication par Mobile Money pour rendre votre événement visible dans le catalogue public.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
                <!-- Récapitulatif de l'événement -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden">
                        <div class="relative h-32 bg-gradient-to-br bg-brand">
                            @if ($event->image_path)
                                <img src="{{ asset('storage/'.$event->image_path) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <x-icon name="photo" class="w-10 h-10 text-white/50" />
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                        </div>
                        <div class="p-5">
                            <h2 class="font-bold text-white line-clamp-1">{{ $event->title }}</h2>

                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="calendar" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span>{{ $event->event_date->translatedFormat('d/m/Y à H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="map-pin" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span class="truncate">{{ $event->location }}</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="users" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span>{{ $event->capacity }} places</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="ticket" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span><x-money :amount="$event->price" /> / billet</span>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Montant des frais -->
                    <div class="rounded-3xl bg-gradient-to-br bg-brand hover:bg-brand-700 p-6 text-white ">
                        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-100">
                            <x-icon name="banknotes" class="w-4 h-4" /> Frais de publication
                        </p>
                        <p class="mt-2 text-4xl font-extrabold tracking-tight">
                            <x-money :amount="$event->publication_fee" />
                        </p>
                        <p class="mt-2 text-sm text-brand-100/90">Paiement unique par Mobile Money — votre événement reste publié sans frais récurrents.</p>
                    </div>
                </div>

                <!-- Formulaire de paiement -->
                @php
                    $platform = config('eventpulse.platform');
                    $platformMobileLabel = \App\Models\Ticket::MOBILE_PROVIDERS[$platform['mobile_money_provider'] ?? ''] ?? null;
                @endphp
                <div class="lg:col-span-3 bg-white rounded-3xl p-6 sm:p-8 shadow-2xl">
                    <form method="POST" action="{{ route('organizer.events.pay.process', $event) }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="payment_method" value="mobile_money">

                        <div>
                            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">
                                <x-icon name="device-phone-mobile" class="w-4 h-4 text-brand" /> Paiement Mobile Money
                            </h3>

                            @if ($platform['mobile_money_phone'])
                                <div class="rounded-2xl border border-brand/20 bg-brand-50/60 px-4 py-3.5 text-sm mb-5">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-brand">Envoyez le paiement à</p>
                                    <p class="font-semibold text-slate-900">{{ $platform['name'] }}</p>
                                    <p class="mt-0.5 font-mono text-slate-800">
                                        @if ($platformMobileLabel)
                                            {{ $platformMobileLabel }} ·
                                        @endif
                                        {{ $platform['mobile_money_phone'] }}
                                    </p>
                                    <p class="mt-1.5 text-xs text-slate-500">Montant : <x-money :amount="$event->publication_fee" /></p>
                                </div>
                            @else
                                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 mb-5">
                                    Le numéro Mobile Money Event Pulse n'est pas encore configuré (EVENTPULSE_MOBILE_PHONE dans .env).
                                </p>
                            @endif

                            <div>
                                <x-input-label value="Votre opérateur" />
                                <div class="grid grid-cols-3 gap-2.5 mt-1.5" data-mobile-providers>
                                    <x-mobile-money-provider
                                        value="mpesa"
                                        label="M-Pesa"
                                        logo="mpesa.png"
                                        :selected="old('mobile_provider') === 'mpesa'" />
                                    <x-mobile-money-provider
                                        value="orange_money"
                                        label="Orange Money"
                                        logo="orange-money.svg"
                                        :selected="old('mobile_provider', 'orange_money') === 'orange_money'" />
                                    <x-mobile-money-provider
                                        value="airtel_money"
                                        label="Airtel Money"
                                        logo="airtel-money.svg"
                                        :selected="old('mobile_provider') === 'airtel_money'" />
                                </div>
                                <x-input-error :messages="$errors->get('mobile_provider')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="phone_number" value="Votre numéro (confirmation)" />
                                <div class="relative mt-1.5">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <x-icon name="phone" class="w-4 h-4" />
                                    </span>
                                    <x-text-input id="phone_number" name="phone_number" type="tel" class="block w-full pl-10"
                                        :value="old('phone_number', auth()->user()->phone)" placeholder="Ex. 06 12 34 56 78" />
                                </div>
                                <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                            </div>
                        </div>

                        <x-primary-button type="submit" class="w-full justify-center !py-3.5">
                            <x-icon name="lock-closed" class="w-4 h-4" />
                            Payer <x-money :amount="$event->publication_fee" :free="false" /> et publier
                        </x-primary-button>

                        <p class="flex items-center justify-center gap-1.5 text-xs text-slate-400">
                            <x-icon name="shield-check" class="w-3.5 h-3.5 text-emerald-500" />
                            Transaction simulée à des fins de démonstration — aucun montant réel n'est prélevé.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
