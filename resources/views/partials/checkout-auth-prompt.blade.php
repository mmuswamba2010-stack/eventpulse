@props([
    'prompt' => __('Checkout login required'),
])

<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <p class="text-sm text-frost text-center">{{ $prompt }}</p>

    <a href="{{ route('login') }}" class="ep-btn w-full justify-center py-3 no-underline">
        {{ __('Log in') }} <x-icon name="arrow-right" class="w-4 h-4" />
    </a>

    <a href="{{ route('register.participant') }}" class="ep-btn-outline w-full justify-center py-3 no-underline">
        {{ __('Create participant account') }}
    </a>

    @include('partials.social-auth-buttons', ['context' => 'login', 'class' => 'mt-1'])

    <a href="{{ route('events.index') }}" class="block text-center text-sm font-medium text-frost hover:text-brand no-underline pt-2">
        {{ __('Back to home') }}
    </a>
</div>
