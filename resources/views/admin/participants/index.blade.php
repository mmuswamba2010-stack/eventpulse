<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-coral mb-1">{{ __('Administration') }}</p>
            <h2 class="font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA]">{{ __('Participants') }}</h2>
            <p class="mt-1 text-sm text-frost">{{ __('Participants admin subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            @include('admin._nav', ['active' => 'participants'])

            <div class="ep-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-charcoal/[0.03] dark:bg-white/5">
                            <tr class="text-left text-frost">
                                <th class="px-4 py-3 font-semibold">{{ __('Name') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Email') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Tickets column') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Alert column') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Registered on') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($participants as $participant)
                                @php $flag = $suspicious->get($participant->id); @endphp
                                <tr @class([
                                    'border-t border-charcoal/5 dark:border-white/5',
                                    'bg-amber-50/50 dark:bg-amber-950/20' => $flag,
                                ])>
                                    <td class="px-4 py-3 font-medium text-charcoal dark:text-[#FAFAFA]">{{ $participant->name }}</td>
                                    <td class="px-4 py-3 text-frost">{{ $participant->email }}</td>
                                    <td class="px-4 py-3 text-frost">
                                        {{ __('Active tickets count', ['count' => $participant->tickets_count]) }}
                                        @if ($participant->cancelled_tickets_count > 0)
                                            · {{ __('Cancelled tickets count', ['count' => $participant->cancelled_tickets_count]) }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($flag)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 dark:text-amber-300">
                                                <x-icon name="exclamation-triangle" class="w-3.5 h-3.5" />
                                                {{ implode(', ', $flag->alert_reasons) }}
                                            </span>
                                        @else
                                            <span class="text-frost text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-frost">{{ $participant->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-frost">{{ __('No participants') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $participants->links() }}</div>
        </div>
    </div>
</x-app-layout>
