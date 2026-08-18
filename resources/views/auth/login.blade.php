<x-guest-layout>
    <div class="mb-7">
        <h1 class="font-display text-2xl font-bold text-charcoal">Connexion</h1>
        <p class="mt-1.5 text-sm text-frost">Accédez à vos billets ou votre espace organisateur.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="envelope" class="w-4.5 h-4.5" />
                </span>
                <x-text-input id="email" class="block w-full pl-10" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="vous@exemple.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Mot de passe" />
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="lock-closed" class="w-4.5 h-4.5" />
                </span>
                <x-password-input id="password" class="block w-full pl-10"
                                name="password"
                                required autocomplete="current-password" placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded-md border-slate-300 text-brand focus:ring-brand" name="remember">
                <span class="text-sm text-slate-600">Se souvenir de moi</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-brand hover:text-brand-700" href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full py-3">
            Connexion <x-icon name="arrow-right" class="w-4 h-4" />
        </x-primary-button>

        <p class="text-center text-sm text-slate-500">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="font-semibold text-brand hover:text-brand-700">Inscrivez-vous</a>
        </p>
    </form>
</x-guest-layout>
