<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-full font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
