<x-app-layout>
    <div class="relative overflow-hidden bg-ink ep-hero">

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <a href="{{ route('organizer.events.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-300 hover:text-white transition mb-8">
                <x-icon name="arrow-left" class="w-4 h-4" /> {{ __('Back to my events') }}
            </a>

            <div class="text-center mb-10">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/10 text-xs font-semibold text-brand-200 mb-4">
                    <x-icon name="shield-check" class="w-3.5 h-3.5" /> {{ __('Secure payment simulation') }}
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    {{ __('Publish event heading', ['title' => $event->title]) }}
                </h1>
                <p class="mt-3 text-slate-300 max-w-xl mx-auto">
                    {{ __('Publication payment intro') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
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
                                    <span>{{ __('Seats count', ['count' => $event->capacity]) }}</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-slate-300">
                                    <x-icon name="ticket" class="w-4 h-4 shrink-0 text-brand-200" />
                                    <span><x-money :amount="$event->price" /> {{ __('Per ticket') }}</span>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="rounded-3xl bg-gradient-to-br bg-brand hover:bg-brand-700 p-6 text-white ">
                        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-100">
                            <x-icon name="banknotes" class="w-4 h-4" /> {{ __('Publication fee label') }}
                        </p>
                        <p class="mt-2 text-4xl font-extrabold tracking-tight">
                            <x-money :amount="$event->publication_fee" />
                        </p>
                        <p class="mt-2 text-sm text-brand-100/90">{{ __('Publication fee once') }}</p>
                    </div>
                </div>

                @php
                    $platform = config('eventpulse.platform');
                    $platformMobileLabel = \App\Models\Ticket::MOBILE_PROVIDERS[$platform['mobile_money_provider'] ?? ''] ?? null;
                @endphp
                <div class="lg:col-span-3 ep-card rounded-3xl p-6 sm:p-8 shadow-2xl">
                    @include('partials.payment-simulation-notice')
                    <form method="POST" action="{{ route('organizer.events.pay.process', $event) }}" class="space-y-6 mt-4">
                        @csrf
                        <input type="hidden" name="payment_method" value="mobile_money">

                        <div>
                            <h3 class="flex items-center gap-2 text-sm font-bold text-charcoal dark:text-[#FAFAFA] uppercase tracking-wide mb-4">
                                <x-icon name="device-phone-mobile" class="w-4 h-4 text-brand" /> {{ __('Mobile money payment section') }}
                            </h3>

                            @if ($platform['mobile_money_phone'])
                                <div class="rounded-2xl border border-brand/20 bg-brand-50/60 px-4 py-3.5 text-sm mb-5">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-brand">{{ __('Send payment to') }}</p>
                                    <p class="font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $platform['name'] }}</p>
                                    <p class="mt-0.5 font-mono text-charcoal dark:text-[#FAFAFA]">
                                        @if ($platformMobileLabel)
                                            {{ $platformMobileLabel }} ·
                                        @endif
                                        {{ $platform['mobile_money_phone'] }}
                                    </p>
                                    <p class="mt-1.5 text-xs text-frost">{{ __('Amount label') }} <x-money :amount="$event->publication_fee" /></p>
                                </div>
                            @else
                                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 mb-5">
                                    {{ __('Platform mobile not configured') }}
                                </p>
                            @endif

                            <div>
                                <x-input-label :value="__('Your operator')" />
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
                                <x-input-label for="phone_number" :value="__('Your number (confirmation)')" />
                                <div class="relative mt-1.5">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <x-icon name="phone" class="w-4 h-4" />
                                    </span>
                                    <x-text-input id="phone_number" name="phone_number" type="tel" class="block w-full pl-10"
                                        :value="old('phone_number', auth()->user()->phone)" placeholder="{{ config('eventpulse.phone.placeholder') }}" />
                                </div>
                                <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                            </div>
                        </div>

                        <x-primary-button type="submit" class="w-full justify-center !py-3.5">
                            <x-icon name="lock-closed" class="w-4 h-4" />
                            {{ __('Pay and publish fee') }} <x-money :amount="$event->publication_fee" :free="false" />
                        </x-primary-button>

                        <p class="flex items-center justify-center gap-1.5 text-xs text-frost">
                            <x-icon name="shield-check" class="w-3.5 h-3.5 text-emerald-500" />
                            {{ __('Simulated transaction notice') }}
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
