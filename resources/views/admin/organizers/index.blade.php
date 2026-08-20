<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-coral mb-1">{{ __('Administration') }}</p>
            <h2 class="font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA]">{{ __('Organizers') }}</h2>
        </div>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            @include('admin._nav', ['active' => 'organizers'])

            <div class="ep-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-charcoal/[0.03] dark:bg-white/5">
                            <tr class="text-left text-frost">
                                <th class="px-4 py-3 font-semibold">{{ __('Name') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Email address') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Organizers') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Tickets sold') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Pending approval') }}</th>
                                <th class="px-4 py-3 font-semibold text-right">{{ __('Edit') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($organizers as $organizer)
                                @php
                                    $statusLabel = match (true) {
                                        $organizer->isSuspended() => __('Suspended'),
                                        $organizer->organizer_status === 'pending' => __('Pending approval'),
                                        $organizer->organizer_status === 'rejected' => __('Rejected'),
                                        default => __('Approved'),
                                    };
                                @endphp
                                <tr class="border-t border-charcoal/5 dark:border-white/5">
                                    <td class="px-4 py-3 font-medium text-charcoal dark:text-[#FAFAFA]">{{ $organizer->name }}</td>
                                    <td class="px-4 py-3 text-frost">{{ $organizer->email }}</td>
                                    <td class="px-4 py-3 text-frost">{{ $organizer->events_count }}</td>
                                    <td class="px-4 py-3 text-frost">{{ $organizer->active_tickets_count ?? 0 }}</td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'text-xs font-semibold px-2 py-1 rounded-full',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' => $organizer->organizer_status === 'pending',
                                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' => $organizer->organizer_status === 'approved' || $organizer->organizer_status === null,
                                            'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300' => $organizer->organizer_status === 'rejected' || $organizer->isSuspended(),
                                        ])>{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($organizer->organizer_status === 'pending')
                                                <form method="POST" action="{{ route('admin.organizers.approve', $organizer) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-xs font-semibold text-emerald-600 hover:underline">{{ __('Approve') }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.organizers.reject', $organizer) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">{{ __('Reject') }}</button>
                                                </form>
                                            @endif

                                            @if (! $organizer->isSuspended())
                                                <form method="POST" action="{{ route('admin.organizers.suspend', $organizer) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-xs font-semibold text-amber-600 hover:underline">{{ __('Suspend') }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.organizers.unsuspend', $organizer) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-xs font-semibold text-brand hover:underline">{{ __('Unsuspend') }}</button>
                                                </form>
                                            @endif

                                            @if (($organizer->active_tickets_count ?? 0) === 0)
                                                <button type="button"
                                                        x-data=""
                                                        x-on:click="$dispatch('open-modal', 'delete-organizer-{{ $organizer->id }}')"
                                                        class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline">
                                                    {{ __('Delete') }}
                                                </button>

                                                <x-modal name="delete-organizer-{{ $organizer->id }}" focusable>
                                                    <form method="POST" action="{{ route('admin.organizers.destroy', $organizer) }}" class="p-6 sm:p-8">
                                                        @csrf
                                                        @method('DELETE')

                                                        <h3 class="text-lg font-bold text-charcoal dark:text-[#FAFAFA]">{{ __('Delete') }} {{ $organizer->name }} ?</h3>

                                                        <div class="mt-4">
                                                            <x-input-label for="confirm-{{ $organizer->id }}" value="DELETE" />
                                                            <x-text-input id="confirm-{{ $organizer->id }}" name="confirm" class="mt-1.5 block w-full" required placeholder="DELETE" />
                                                        </div>

                                                        <div class="mt-6 flex justify-end gap-3">
                                                            <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                                                            <x-danger-button>{{ __('Delete') }}</x-danger-button>
                                                        </div>
                                                    </form>
                                                </x-modal>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-frost">{{ __('No alerts') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $organizers->links() }}</div>
        </div>
    </div>
</x-app-layout>
