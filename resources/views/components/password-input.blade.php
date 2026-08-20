@props(['disabled' => false])

<div class="relative w-full ep-password-field" data-password-field>
    <input
        type="password"
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'ep-input rounded-lg shadow-sm pr-11 w-full']) }}
    >
    <button
        type="button"
        data-password-toggle
        class="ep-password-toggle absolute inset-y-0 right-0 z-20 flex h-full items-center"
        aria-label="Afficher le mot de passe"
        aria-pressed="false"
    >
        <span data-password-icon-show class="inline-flex" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </span>
        <span data-password-icon-hide class="hidden inline-flex" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.952-.138 2.863-.395m-2.032-2.033A10.45 10.45 0 0112 4.5c-2.23 0-4.283.698-5.964 1.886M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9.75 9.75l14.5-14.5"/>
            </svg>
        </span>
    </button>
</div>
