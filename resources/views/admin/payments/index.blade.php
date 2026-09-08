<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-coral mb-1">{{ __('Administration') }}</p>
            <h2 class="font-extrabold text-2xl text-charcoal dark:text-[#FAFAFA]">{{ __('Admin payments title') }}</h2>
            <p class="mt-1 text-sm text-frost">{{ __('Admin payments subtitle') }}</p>
        </div>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            @include('admin._nav', ['active' => 'payments'])

            <div class="flex flex-wrap gap-2 mb-6">
                @foreach ([
                    'pending' => __('Payment pending'),
                    'succeeded' => __('Payment succeeded'),
                    'failed' => __('Payment failed'),
                    'all' => __('View all'),
                ] as $filter => $label)
                    <a href="{{ route('admin.payments.index', $filter === 'pending' ? [] : ['status' => $filter]) }}"
                       @class([
                           'inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold transition no-underline',
                           'bg-brand text-white' => $status === $filter,
                           'bg-white dark:bg-[#1A1A1A] text-frost border border-charcoal/10 dark:border-white/10 hover:text-charcoal dark:hover:text-[#FAFAFA]' => $status !== $filter,
                       ])>
                        {{ $label }}
                        @if ($filter === 'pending' && $pendingCount > 0)
                            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[11px] font-bold bg-white/20">{{ $pendingCount }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="ep-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-charcoal/[0.03] dark:bg-white/5">
                            <tr class="text-left text-frost">
                                <th class="px-4 py-3 font-semibold">{{ __('Payment reference') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Admin payment purpose') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Name') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Payment amount') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Admin payment provider') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Date column') }}</th>
                                <th class="px-4 py-3 font-semibold">{{ __('Admin payment status') }}</th>
                                <th class="px-4 py-3 font-semibold text-right">{{ __('Edit') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                @php
                                    $payable = $payment->payable;
                                    $purposeLabel = $payment->purpose === \App\Models\Payment::PURPOSE_PUBLICATION
                                        ? __('Publication fee label')
                                        : __('Admin payment tickets');
                                    $statusClass = match ($payment->status) {
                                        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
                                        'succeeded' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
                                        default => 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300',
                                    };
                                    $statusLabel = match ($payment->status) {
                                        'pending' => __('Payment pending'),
                                        'succeeded' => __('Payment succeeded'),
                                        'failed' => __('Payment failed'),
                                        default => $payment->status,
                                    };
                                @endphp
                                <tr class="border-t border-charcoal/5 dark:border-white/5 align-top">
                                    <td class="px-4 py-3 font-mono font-semibold text-charcoal dark:text-[#FAFAFA]">{{ $payment->reference }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-charcoal dark:text-[#FAFAFA]">{{ $purposeLabel }}</p>
                                        @if ($payable instanceof \App\Models\Event)
                                            <p class="text-xs text-frost mt-0.5">{{ $payable->title }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-frost">
                                        <p>{{ $payment->user?->name }}</p>
                                        <p class="text-xs">{{ $payment->phone }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-charcoal dark:text-[#FAFAFA]">
                                        @if ($payment->purpose === \App\Models\Payment::PURPOSE_PUBLICATION)
                                            <x-money :amount="$payment->amount" primary="usd" :free="false" />
                                        @else
                                            <x-money :amount="$payment->amount" :free="false" />
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-frost">
                                        {{ $payment->provider ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-frost whitespace-nowrap">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span @class(['text-xs font-semibold px-2 py-1 rounded-full', $statusClass])>{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($payment->isPending())
                                            <div class="flex flex-col items-end gap-2">
                                                <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" class="flex flex-col sm:flex-row items-end gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="text"
                                                           name="external_id"
                                                           placeholder="{{ __('Admin payment transaction id') }}"
                                                           class="ep-input rounded-lg text-xs py-1.5 px-2 w-full sm:w-36">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                                                        <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                                        {{ __('Admin payment confirm') }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.payments.fail', $payment) }}"
                                                      onsubmit="return confirm(@js(__('Admin payment reject confirm')));">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 text-rose-600 dark:text-rose-400 text-xs font-bold transition">
                                                        <x-icon name="x-circle" class="w-3.5 h-3.5" />
                                                        {{ __('Admin payment reject') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-frost block text-right">
                                                @if ($payment->confirmed_at)
                                                    {{ $payment->confirmed_at->format('d/m/Y H:i') }}
                                                @endif
                                                @if ($payment->external_id)
                                                    <span class="block font-mono mt-0.5">{{ $payment->external_id }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-frost">{{ __('Admin payments empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($payments->hasPages())
                <div class="mt-6">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
