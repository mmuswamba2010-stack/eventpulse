<section>
    <header class="flex items-center gap-3">
        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-100 dark:bg-violet/20 text-brand shrink-0">
            <x-icon name="lock-closed" class="w-5 h-5" />
        </span>
        <div>
            <h2 class="text-lg font-bold text-charcoal dark:text-[#FAFAFA]">
                {{ __('Password') }}
            </h2>
            <p class="text-sm text-frost">
                {{ __('Use a long, unique password to stay secure.') }}
            </p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current password')" />
            <x-password-input id="update_password_current_password" name="current_password" class="mt-1.5 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New password')" />
            <x-password-input id="update_password_password" name="password" class="mt-1.5 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm password')" />
            <x-password-input id="update_password_password_confirmation" name="password_confirmation" class="mt-1.5 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
