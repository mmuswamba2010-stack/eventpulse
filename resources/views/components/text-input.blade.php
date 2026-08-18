@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'ep-input rounded-lg shadow-sm']) }}>
