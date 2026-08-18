<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ep-btn']) }}>
    {{ $slot }}
</button>
