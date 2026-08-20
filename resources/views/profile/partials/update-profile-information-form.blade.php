<section>
    <header class="flex items-center gap-3">
        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-100 dark:bg-violet/20 text-brand shrink-0">
            <x-icon name="identification" class="w-5 h-5" />
        </span>
        <div>
            <h2 class="text-lg font-bold text-charcoal dark:text-[#FAFAFA]">
                {{ __('Profile information') }}
            </h2>
            <p class="text-sm text-frost">
                {{ __('Update your name and email address.') }}
            </p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-frost">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="font-semibold text-brand hover:text-brand-700 dark:hover:text-violet">
                            {{ __('Click here to resend the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-600 dark:text-emerald-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
