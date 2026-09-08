@props(['active' => 'dashboard'])

<nav class="flex flex-wrap gap-2 mb-8">
    @php
        $tabs = [
            'dashboard' => ['label' => __('Dashboard'), 'route' => 'admin.dashboard'],
            'payments' => ['label' => __('Admin payments nav'), 'route' => 'admin.payments.index', 'badge' => \App\Models\Payment::query()->where('status', \App\Models\Payment::STATUS_PENDING)->count()],
            'newsletter' => ['label' => __('Newsletter'), 'route' => 'admin.newsletter.index'],
            'organizers' => ['label' => __('Organizers'), 'route' => 'admin.organizers.index'],
            'participants' => ['label' => __('Participants'), 'route' => 'admin.participants.index'],
        ];
    @endphp

    @foreach ($tabs as $key => $tab)
        <a href="{{ route($tab['route']) }}"
           @class([
               'inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold transition no-underline',
               'bg-charcoal text-white dark:bg-violet dark:text-white' => $active === $key,
               'bg-white dark:bg-[#1A1A1A] text-frost border border-charcoal/10 dark:border-white/10 hover:text-charcoal dark:hover:text-[#FAFAFA]' => $active !== $key,
           ])>
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>

@if (session('admin_error'))
    <div class="mb-6 flex items-center gap-2 font-medium text-sm text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl px-4 py-3">
        <x-icon name="exclamation-triangle" class="w-4 h-4 shrink-0" />
        {{ session('admin_error') }}
    </div>
@endif

@if (session('admin_success'))
    <div class="mb-6 flex items-center gap-2 font-medium text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl px-4 py-3">
        <x-icon name="check-circle" class="w-4 h-4 shrink-0" />
        {{ session('admin_success') }}
    </div>
@endif
