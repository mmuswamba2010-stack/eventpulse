@props(['disabled' => false])

<div class="relative" x-data="{ visible: false }">
    <input
        x-bind:type="visible ? 'text' : 'password'"
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'ep-input rounded-lg shadow-sm pr-11']) }}
    >
    <button
        type="button"
        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-frost hover:text-charcoal transition"
        @click="visible = !visible"
        :aria-label="visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
        :aria-pressed="visible ? 'true' : 'false'"
    >
        <span x-show="!visible" class="inline-flex">
            <x-icon name="eye" class="w-4.5 h-4.5" />
        </span>
        <span x-show="visible" x-cloak class="inline-flex">
            <x-icon name="eye-slash" class="w-4.5 h-4.5" />
        </span>
    </button>
</div>
