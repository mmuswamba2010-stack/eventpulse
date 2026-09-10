@if (session('error') || session('info') || session('success'))
    <div class="mb-5 space-y-3">
        @if (session('success'))
            <div role="alert" class="rounded-xl border border-violet/25 bg-violet-muted/50 dark:bg-violet/15 px-4 py-3 text-sm text-charcoal dark:text-[#FAFAFA]">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-coral/30 bg-coral/5 dark:bg-coral/10 px-4 py-3 text-sm text-charcoal dark:text-[#FAFAFA]">
                {{ session('error') }}
                @if (session('error') === __('You already have a ticket for this event. Check My tickets.'))
                    <a href="{{ route('tickets.index') }}" class="mt-2 inline-block font-semibold text-brand hover:underline">{{ __('My tickets') }}</a>
                @endif
            </div>
        @endif
        @if (session('info'))
            <div role="alert" class="rounded-xl border border-sky-200 dark:border-sky-500/30 bg-sky-50 dark:bg-sky-500/10 px-4 py-3 text-sm text-sky-900 dark:text-sky-100">
                {{ session('info') }}
            </div>
        @endif
    </div>
@endif
