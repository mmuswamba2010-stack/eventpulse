<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-coral mb-1">{{ __('Administration') }}</p>
            <h2 class="font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA]">{{ __('Newsletter') }}</h2>
            <p class="mt-1 text-sm text-frost">{{ __('Newsletter admin subtitle', ['count' => $activeCount]) }}</p>
        </div>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            @include('admin._nav', ['active' => 'newsletter'])

            <div class="grid lg:grid-cols-2 gap-6">
                <section class="ep-card p-6">
                    <h3 class="font-bold text-charcoal dark:text-[#FAFAFA] mb-4">{{ __('Send newsletter') }}</h3>

                    <form method="POST" action="{{ route('admin.newsletter.send') }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="intro" :value="__('Intro message optional')" />
                            <textarea id="intro" name="intro" rows="3"
                                      class="ep-input mt-1.5 block w-full rounded-xl"
                                      placeholder="{{ __('Newsletter intro placeholder') }}">{{ old('intro') }}</textarea>
                        </div>

                        <div>
                            <x-input-label for="days" :value="__('Events published last days')" />
                            <select id="days" name="days" class="ep-input mt-1.5 block w-full rounded-xl">
                                @foreach ([7, 14, 30] as $option)
                                    <option value="{{ $option }}" @selected(old('days', 7) == $option)>{{ __('Days option', ['count' => $option]) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('days')" class="mt-2" />
                        </div>

                        @if ($recentEvents->isNotEmpty())
                            <div class="rounded-xl bg-charcoal/[0.03] dark:bg-white/5 p-4 text-sm">
                                <p class="font-semibold text-charcoal dark:text-[#FAFAFA] mb-2">{{ __('Preview seven days') }}</p>
                                <ul class="space-y-1 text-frost">
                                    @foreach ($recentEvents->take(5) as $event)
                                        <li>• {{ $event->title }} — {{ $event->event_date->translatedFormat('d/m/Y') }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <x-primary-button type="submit" class="w-full justify-center py-3">
                            {{ __('Send to subscribers', ['count' => $activeCount]) }}
                        </x-primary-button>
                    </form>
                </section>

                <section class="ep-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Subscribers list') }}</h3>
                        <a href="{{ route('admin.newsletter.export') }}" class="text-sm font-semibold text-brand">{{ __('Export CSV') }}</a>
                    </div>

                    @if ($subscribers->isEmpty())
                        <p class="text-sm text-frost">{{ __('No subscribers') }}</p>
                    @else
                        <ul class="space-y-2 text-sm max-h-96 overflow-y-auto">
                            @foreach ($subscribers as $subscriber)
                                <li class="flex justify-between gap-2 py-2 border-b border-charcoal/5 dark:border-white/5">
                                    <span class="text-charcoal dark:text-[#FAFAFA] truncate">{{ $subscriber->email }}</span>
                                    <span class="text-frost text-xs shrink-0">{{ $subscriber->created_at->format('d/m/Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-4">{{ $subscribers->links() }}</div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
