@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 font-medium text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3']) }}>
        <x-icon name="check-circle" class="w-4 h-4 shrink-0" />
        {{ $status }}
    </div>
@endif
