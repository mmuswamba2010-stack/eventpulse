<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="flex items-center gap-2 font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA] tracking-tight">
                    <x-icon name="squares-2x2" class="w-7 h-7 text-brand" />
                    {{ __('Organizer dashboard title') }}
                </h2>
                <p class="mt-1 text-sm text-frost">{{ __('Organizer dashboard subtitle') }}</p>
            </div>
            <a href="{{ route('organizer.events.create') }}"
               class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r bg-brand hover:bg-brand-700 text-white text-sm font-semibold rounded-full  hover:brightness-110 transition self-start">
                <x-icon name="plus" class="w-4 h-4" /> {{ __('New event') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4 space-y-8">
            <!-- Statistiques -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="ep-card shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-brand-100 text-brand">
                            <x-icon name="calendar" class="w-5 h-5" />
                        </span>
                    </div>
                    <p class="text-3xl font-extrabold text-charcoal dark:text-[#FAFAFA] mt-4">{{ $totalEvents }}</p>
                    <p class="text-sm text-frost mt-1">{{ __('Events created') }}</p>
                </div>
                <div class="ep-card shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-sky-100 text-sky-600">
                            <x-icon name="ticket" class="w-5 h-5" />
                        </span>
                    </div>
                    <p class="text-3xl font-extrabold text-charcoal dark:text-[#FAFAFA] mt-4">{{ $totalTicketsSold }}</p>
                    <p class="text-sm text-frost mt-1">{{ __('Tickets sold') }}</p>
                </div>
                <div class="ep-card shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600">
                            <x-icon name="check-badge" class="w-5 h-5" />
                        </span>
                    </div>
                    <p class="text-3xl font-extrabold text-charcoal dark:text-[#FAFAFA] mt-4">{{ $totalTicketsUsed }}</p>
                    <p class="text-sm text-frost mt-1">{{ __('Tickets scanned') }}</p>
                </div>
                <div class="ep-card shadow-sm p-6 relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br bg-brand rounded-full blur-2xl opacity-20"></div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-br bg-brand text-white">
                            <x-icon name="banknotes" class="w-5 h-5" />
                        </span>
                    </div>
                    <p class="text-3xl font-extrabold text-charcoal dark:text-[#FAFAFA] mt-4">
                        <x-money :amount="$totalRevenue" />
                    </p>
                    <p class="text-sm text-frost mt-1">{{ __('Revenue dual label') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Derniers événements -->
                <div class="lg:col-span-2 ep-card shadow-sm">
                    <div class="px-6 py-5 border-b border-charcoal/5 dark:border-white/5 flex items-center justify-between">
                        <h3 class="font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('My latest events') }}</h3>
                        <a href="{{ route('organizer.events.index') }}" class="text-sm font-semibold text-brand hover:text-brand-700">{{ __('View all') }}</a>
                    </div>
                    @if ($events->isEmpty())
                        <p class="p-8 text-sm text-frost text-center">{{ __('No events yet dashboard') }}</p>
                    @else
                        <div class="divide-y divide-charcoal/5 dark:divide-white/5">
                            @foreach ($events as $event)
                                @php $pct = $event->capacity > 0 ? min(100, round(($event->sold_count / $event->capacity) * 100)) : 0; @endphp
                                <div class="px-6 py-4 flex items-center gap-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-charcoal dark:text-[#FAFAFA] truncate">{{ $event->title }}</p>
                                        <p class="text-xs text-frost mt-0.5">{{ $event->event_date->format('d/m/Y H:i') }}</p>
                                        <div class="mt-2 h-1.5 rounded-full bg-charcoal/5 dark:bg-white/10 overflow-hidden max-w-xs">
                                            <div class="h-full rounded-xl bg-brand" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-bold text-charcoal dark:text-[#FAFAFA]">{{ $event->sold_count }}/{{ $event->capacity }}</p>
                                        <p class="text-xs text-frost"><x-money :amount="$event->revenue ?? 0" /></p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Derniers scans -->
                <div class="ep-card shadow-sm">
                    <div class="px-6 py-5 border-b border-charcoal/5 dark:border-white/5 flex items-center justify-between">
                        <h3 class="font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Recent scans') }}</h3>
                        <a href="{{ route('organizer.scan.index') }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-700">
                            <x-icon name="camera" class="w-3.5 h-3.5" /> {{ __('Scanner') }}
                        </a>
                    </div>
                    @if ($recentScans->isEmpty())
                        <p class="p-8 text-sm text-frost text-center">{{ __('No scans yet') }}</p>
                    @else
                        <ul class="divide-y divide-charcoal/5 dark:divide-white/5">
                            @foreach ($recentScans as $scan)
                                <li class="px-6 py-3.5 flex items-center gap-3">
                                    @if ($scan->status === 'success')
                                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 shrink-0">
                                            <x-icon name="check-circle" class="w-4 h-4" />
                                        </span>
                                    @elseif ($scan->status === 'already_used')
                                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-600 shrink-0">
                                            <x-icon name="shield-exclamation" class="w-4 h-4" />
                                        </span>
                                    @else
                                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-charcoal/5 dark:bg-white/10 text-frost shrink-0">
                                            <x-icon name="x-circle" class="w-4 h-4" />
                                        </span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-charcoal dark:text-[#FAFAFA] truncate">{{ $scan->ticket->user->name }}</p>
                                        <p class="text-xs text-frost truncate">{{ $scan->ticket->event->title }} · {{ $scan->created_at->diffForHumans() }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
