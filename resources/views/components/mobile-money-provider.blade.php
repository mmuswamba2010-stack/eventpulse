@props([
    'selected' => false,
    'name' => 'mobile_provider',
    'value',
    'label',
    'logo',
    'compact' => false,
])

<label {{ $attributes->class([
    'group relative flex cursor-pointer items-center justify-center border-2 rounded-xl bg-white text-center select-none transition',
    'flex-col gap-1.5 px-2 py-2' => $compact,
    'flex-col gap-2 px-2 py-3' => ! $compact,
    'border-brand bg-brand-50 ring-2 ring-brand/20' => $selected,
    'border-slate-200 hover:border-slate-300' => ! $selected,
]) }}
    onclick="
        const root = this.closest('[data-mobile-providers]');
        root.querySelectorAll('label').forEach(l => {
            l.classList.remove('border-brand','bg-brand-50','ring-2','ring-brand/20');
            l.classList.add('border-slate-200');
            const t = l.querySelector('[data-provider-label]');
            if (t) { t.classList.remove('text-brand-700'); t.classList.add('text-slate-700'); }
        });
        this.classList.add('border-brand','bg-brand-50','ring-2','ring-brand/20');
        this.classList.remove('border-slate-200');
        const label = this.querySelector('[data-provider-label]');
        if (label) { label.classList.add('text-brand-700'); label.classList.remove('text-slate-700'); }
    ">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" class="sr-only" @checked($selected)>
    <span @class([
        'flex items-center justify-center rounded-lg bg-white',
        'h-8 w-full px-1' => $compact,
        'h-16 w-full px-2 rounded-xl' => ! $compact,
    ])>
        <img src="{{ asset('images/payments/'.$logo) }}" alt="{{ $label }}"
             @class([
                 'max-w-full object-contain',
                 'max-h-6' => $compact,
                 'max-h-14' => ! $compact,
             ])
             loading="lazy">
    </span>
    @unless ($compact)
        <span data-provider-label class="text-[11px] font-bold leading-tight {{ $selected ? 'text-brand-700' : 'text-slate-700' }}">
            {{ $label }}
        </span>
    @endunless
</label>
