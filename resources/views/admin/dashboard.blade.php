<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-coral mb-1">{{ __('Administration') }}</p>
            <h2 class="flex items-center gap-2 font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA] tracking-tight">
                <x-icon name="shield-check" class="w-7 h-7 text-brand" />
                {{ __('Admin dashboard title') }}
            </h2>
            <p class="mt-1 text-sm text-frost">{{ __('Admin dashboard subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            @include('admin._nav', ['active' => 'dashboard'])

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                @foreach ([
                    ['label' => __('Organizers count label'), 'value' => $stats['organizers'], 'icon' => 'users'],
                    ['label' => __('Participants count label'), 'value' => $stats['participants'], 'icon' => 'users'],
                    ['label' => __('Published events label'), 'value' => $stats['events_published'], 'icon' => 'calendar'],
                    ['label' => __('Newsletter subscribers label'), 'value' => $stats['newsletter_active'], 'icon' => 'envelope'],
                ] as $stat)
                    <div class="ep-card p-5">
                        <x-icon :name="$stat['icon']" class="w-5 h-5 text-brand mb-3" />
                        <p class="text-2xl font-extrabold text-charcoal dark:text-[#FAFAFA]">{{ $stat['value'] }}</p>
                        <p class="text-sm text-frost mt-1">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <section class="ep-card p-6">
                    <h3 class="font-bold text-charcoal dark:text-[#FAFAFA] flex items-center gap-2 mb-4">
                        <x-icon name="exclamation-triangle" class="w-5 h-5 text-amber-500" />
                        {{ __('Organizers to watch') }}
                    </h3>
                    @if ($flaggedOrganizers->isEmpty())
                        <p class="text-sm text-frost">{{ __('No alerts') }}</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($flaggedOrganizers as $organizer)
                                <li class="flex items-start justify-between gap-3 text-sm border-b border-charcoal/5 dark:border-white/5 pb-3 last:border-0 last:pb-0">
                                    <div>
                                        <p class="font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $organizer->name }}</p>
                                        <p class="text-frost text-xs">{{ $organizer->email }}</p>
                                        <p class="text-amber-600 dark:text-amber-400 text-xs mt-1">{{ implode(' · ', $organizer->alert_reasons) }}</p>
                                    </div>
                                    <a href="{{ route('admin.organizers.index') }}" class="text-xs font-semibold text-brand shrink-0">{{ __('View') }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="ep-card p-6">
                    <h3 class="font-bold text-charcoal dark:text-[#FAFAFA] flex items-center gap-2 mb-4">
                        <x-icon name="exclamation-triangle" class="w-5 h-5 text-amber-500" />
                        {{ __('Suspicious participants') }}
                    </h3>
                    @if ($suspiciousParticipants->isEmpty())
                        <p class="text-sm text-frost">{{ __('No suspicious activity') }}</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($suspiciousParticipants as $participant)
                                <li class="flex items-start justify-between gap-3 text-sm border-b border-charcoal/5 dark:border-white/5 pb-3 last:border-0 last:pb-0">
                                    <div>
                                        <p class="font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $participant->name }}</p>
                                        <p class="text-frost text-xs">{{ $participant->email }}</p>
                                        <p class="text-amber-600 dark:text-amber-400 text-xs mt-1">{{ implode(' · ', $participant->alert_reasons) }}</p>
                                    </div>
                                    <a href="{{ route('admin.participants.index') }}" class="text-xs font-semibold text-brand shrink-0">{{ __('View') }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            <section class="ep-card p-6 mt-6">
                <h3 class="font-bold text-charcoal dark:text-[#FAFAFA] mb-4">{{ __('Recent events seven days') }}</h3>
                @if ($recentEvents->isEmpty())
                    <p class="text-sm text-frost">{{ __('No new events week') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-frost border-b border-charcoal/10 dark:border-white/10">
                                    <th class="pb-2 pr-4 font-semibold">{{ __('Event column') }}</th>
                                    <th class="pb-2 pr-4 font-semibold">{{ __('Organizer column') }}</th>
                                    <th class="pb-2 font-semibold">{{ __('Date column') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentEvents as $event)
                                    <tr class="border-b border-charcoal/5 dark:border-white/5 last:border-0">
                                        <td class="py-3 pr-4 font-medium text-charcoal dark:text-[#FAFAFA]">{{ $event->title }}</td>
                                        <td class="py-3 pr-4 text-frost">{{ $event->user?->name }}</td>
                                        <td class="py-3 text-frost">{{ $event->event_date->translatedFormat('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
