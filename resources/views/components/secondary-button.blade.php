<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-white/10 rounded-full font-semibold text-sm text-slate-700 dark:text-[#FAFAFA] shadow-sm hover:bg-slate-50 dark:hover:bg-[#252525] hover:border-slate-300 dark:hover:border-white/20 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 dark:focus:ring-offset-[#0F0F0F] disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
