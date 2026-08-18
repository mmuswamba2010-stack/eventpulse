@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-rose-600 space-y-1 font-medium']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1.5">
                <x-icon name="exclamation-triangle" class="w-4 h-4 shrink-0 mt-0.5" />
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
