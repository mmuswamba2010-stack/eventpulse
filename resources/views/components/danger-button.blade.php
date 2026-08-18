<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 border border-transparent rounded-full font-semibold text-sm text-white shadow-lg shadow-rose-500/25 hover:brightness-110 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition']) }}>
    {{ $slot }}
</button>
