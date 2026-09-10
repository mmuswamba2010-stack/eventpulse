@if (session('success') || session('error') || session('info'))
    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 mt-6 space-y-3">
        @if (session('success'))
            <div
                role="alert"
                class="flash-alert relative overflow-hidden flex items-start gap-3 px-5 py-3.5 text-sm font-medium text-charcoal dark:text-[#FAFAFA] rounded-2xl border border-violet/20 dark:border-violet/30 border-l-4 border-l-violet shadow-sm bg-gradient-to-r from-violet-muted via-white to-coral-muted dark:from-violet/25 dark:via-[#1A1A1A] dark:to-coral/25"
            >
                <span class="pointer-events-none absolute inset-y-0 right-0 w-2/5 bg-gradient-to-l from-coral-soft/25 via-coral-muted/40 to-transparent dark:from-coral/30 dark:via-coral/10 dark:to-transparent" aria-hidden="true"></span>
                <x-icon name="check-circle" class="relative z-10 w-5 h-5 shrink-0 text-violet dark:text-violet-soft mt-0.5" />
                <p class="relative z-10 flex-1 leading-relaxed">{{ session('success') }}</p>
                <button
                    type="button"
                    onclick="this.closest('[role=alert]')?.remove()"
                    class="relative z-10 shrink-0 rounded-md p-1 text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] transition"
                    aria-label="{{ __('Close') }}"
                >
                    <x-icon name="x-mark" class="w-4 h-4" />
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                role="alert"
                class="flash-alert flex items-start gap-3 ep-card px-5 py-3.5 text-sm font-medium text-charcoal dark:text-[#FAFAFA] border-l-4 border-l-coral bg-coral/5 dark:bg-coral/10"
            >
                <x-icon name="exclamation-triangle" class="w-5 h-5 shrink-0 text-coral mt-0.5" />
                <p class="flex-1 leading-relaxed">{{ session('error') }}</p>
                <button
                    type="button"
                    onclick="this.closest('[role=alert]')?.remove()"
                    class="shrink-0 rounded-md p-1 text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] transition"
                    aria-label="{{ __('Close') }}"
                >
                    <x-icon name="x-mark" class="w-4 h-4" />
                </button>
            </div>
        @endif

        @if (session('info'))
            <div
                role="alert"
                class="flash-alert flex items-start gap-3 ep-card px-5 py-3.5 text-sm font-medium text-charcoal dark:text-[#FAFAFA] border-l-4 border-l-sky-500 bg-sky-50 dark:bg-sky-500/10"
            >
                <x-icon name="check-circle" class="w-5 h-5 shrink-0 text-sky-600 dark:text-sky-400 mt-0.5" />
                <p class="flex-1 leading-relaxed">{{ session('info') }}</p>
                <button
                    type="button"
                    onclick="this.closest('[role=alert]')?.remove()"
                    class="shrink-0 rounded-md p-1 text-frost hover:text-charcoal dark:hover:text-[#FAFAFA] transition"
                    aria-label="{{ __('Close') }}"
                >
                    <x-icon name="x-mark" class="w-4 h-4" />
                </button>
            </div>
        @endif
    </div>
@endif
