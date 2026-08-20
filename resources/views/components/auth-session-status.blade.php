@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 font-medium text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl px-4 py-3']) }}>
        <x-icon name="check-circle" class="w-4 h-4 shrink-0" />
        {{ $status }}
    </div>
@endif
