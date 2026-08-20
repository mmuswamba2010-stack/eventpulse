@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-charcoal dark:text-[#FAFAFA]']) }}>
    {{ $value ?? $slot }}
</label>
