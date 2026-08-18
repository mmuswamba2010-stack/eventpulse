@if (session('success') || session('error'))
    <div
        class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 mt-6 space-y-3"
        x-data="{ showSuccess: true, showError: true }"
    >
        @if (session('success'))
            <div
                x-show="showSuccess"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                role="alert"
                class="flex items-start gap-3 ep-card px-5 py-3.5 text-sm font-medium text-charcoal border-l-4 border-l-violet bg-violet/5"
            >
                <x-icon name="check-circle" class="w-5 h-5 shrink-0 text-violet mt-0.5" />
                <p class="flex-1 leading-relaxed">{{ session('success') }}</p>
                <button
                    type="button"
                    @click="showSuccess = false"
                    class="shrink-0 rounded-md p-1 text-frost hover:text-charcoal transition"
                    aria-label="Fermer la notification"
                >
                    <x-icon name="x-mark" class="w-4 h-4" />
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                x-show="showError"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                role="alert"
                class="flex items-start gap-3 ep-card px-5 py-3.5 text-sm font-medium text-charcoal border-l-4 border-l-coral bg-coral/5"
            >
                <x-icon name="exclamation-triangle" class="w-5 h-5 shrink-0 text-coral mt-0.5" />
                <p class="flex-1 leading-relaxed">{{ session('error') }}</p>
                <button
                    type="button"
                    @click="showError = false"
                    class="shrink-0 rounded-md p-1 text-frost hover:text-charcoal transition"
                    aria-label="Fermer la notification"
                >
                    <x-icon name="x-mark" class="w-4 h-4" />
                </button>
            </div>
        @endif
    </div>
@endif
