<section class="space-y-5">
    <header class="flex items-center gap-3">
        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 shrink-0">
            <x-icon name="exclamation-triangle" class="w-5 h-5" />
        </span>
        <div>
            <h2 class="text-lg font-bold text-charcoal dark:text-[#FAFAFA]">
                {{ __('Delete account') }}
            </h2>
            <p class="text-sm text-frost">
                {{ __('This action is permanent. All your data will be deleted.') }}
            </p>
        </div>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <x-icon name="exclamation-triangle" class="w-4 h-4" /> {{ __('Delete account') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-charcoal dark:text-[#FAFAFA]">
                {{ __('Confirm account deletion') }}
            </h2>

            <p class="mt-1.5 text-sm text-frost">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" :value="__('Password')" class="sr-only" />

                <x-password-input
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('Delete account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
