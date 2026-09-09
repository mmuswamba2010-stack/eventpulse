<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\OrganizerTerms;
use App\Support\SocialAuth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialAuthController extends Controller
{
    public function redirect(Request $request, string $provider): SymfonyRedirectResponse|RedirectResponse
    {
        if (! SocialAuth::isConfigured($provider)) {
            return back()->with('error', __('Social login unavailable'));
        }

        $context = $request->string('context')->toString() ?: 'login';

        if (! in_array($context, SocialAuth::allowedContexts(), true)) {
            abort(404);
        }

        $request->session()->put('social_auth_context', $context);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        if (! SocialAuth::isConfigured($provider)) {
            return redirect()->route('login')->with('error', __('Social login unavailable'));
        }

        $context = $request->session()->pull('social_auth_context', 'login');

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('login')->with('error', __('Social login failed'));
        }

        $column = SocialAuth::providerColumn($provider);
        $user = User::query()->where($column, $socialUser->getId())->first()
            ?? User::query()->where('email', $socialUser->getEmail())->first();

        if ($user) {
            return $this->loginExistingUser($user, $provider, $socialUser, $context);
        }

        return match ($context) {
            'organizer-register' => $this->registerOrganizer($socialUser, $provider),
            'participant-register', 'login' => $this->registerParticipant($socialUser, $provider),
            default => redirect()->route('login')->with('error', __('Social login failed')),
        };
    }

    public function createOrganizerCompletion(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isOrganizer() || $this->organizerProfileIsComplete($user)) {
            return redirect()->route('organizer.dashboard');
        }

        return view('auth.register-organizer-complete');
    }

    public function storeOrganizerCompletion(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isOrganizer()) {
            abort(403);
        }

        $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:30'],
            'accept_organizer_terms' => ['accepted'],
        ], [], [
            'accept_organizer_terms' => __('Organizer terms acceptance label'),
        ]);

        $user->update([
            'phone' => $request->phone,
            'organizer_terms_accepted_at' => now(),
            'organizer_terms_version' => OrganizerTerms::version(),
        ]);

        $route = $user->canAccessOrganizerSpace()
            ? route('organizer.dashboard', absolute: false)
            : route('organizer.pending', absolute: false);

        return redirect()->intended($route)
            ->with('success', __('Account created welcome organizer', ['name' => Str::before(trim($user->name), ' ') ?: $user->name]));
    }

    private function loginExistingUser(User $user, string $provider, SocialiteUser $socialUser, string $context): RedirectResponse
    {
        if ($context === 'organizer-register' && ! $user->isOrganizer()) {
            return redirect()->route('register.organizer')
                ->with('error', __('Social login email already participant'));
        }

        if ($context === 'participant-register' && $user->isOrganizer()) {
            return redirect()->route('register.participant')
                ->with('error', __('Social login email already organizer'));
        }

        $this->linkProvider($user, $provider, $socialUser);

        Auth::login($user, remember: true);

        if ($user->isOrganizer() && ! $this->organizerProfileIsComplete($user)) {
            return redirect()->route('register.organizer.complete');
        }

        return redirect()->intended($this->homeRouteFor($user));
    }

    private function registerParticipant(SocialiteUser $socialUser, string $provider): RedirectResponse
    {
        if (! $socialUser->getEmail()) {
            return redirect()->route('register.participant')
                ->with('error', __('Social login email required'));
        }

        $user = User::create([
            'name' => $this->resolveName($socialUser),
            'email' => Str::lower($socialUser->getEmail()),
            'password' => null,
            'role' => 'participant',
            'email_verified_at' => now(),
            SocialAuth::providerColumn($provider) => $socialUser->getId(),
        ]);

        event(new Registered($user));
        Auth::login($user, remember: true);

        return redirect()->intended(route('events.index', absolute: false))
            ->with('success', __('Account created welcome participant', [
                'name' => Str::before(trim($user->name), ' ') ?: $user->name,
            ]));
    }

    private function registerOrganizer(SocialiteUser $socialUser, string $provider): RedirectResponse
    {
        if (! $socialUser->getEmail()) {
            return redirect()->route('register.organizer')
                ->with('error', __('Social login email required'));
        }

        $user = User::create([
            'name' => $this->resolveName($socialUser),
            'email' => Str::lower($socialUser->getEmail()),
            'password' => null,
            'role' => 'organizer',
            'email_verified_at' => now(),
            SocialAuth::providerColumn($provider) => $socialUser->getId(),
            'organizer_status' => config('eventpulse.organizer_moderation', true) ? 'pending' : 'approved',
        ]);

        event(new Registered($user));
        Auth::login($user, remember: true);

        return redirect()->route('register.organizer.complete');
    }

    private function linkProvider(User $user, string $provider, SocialiteUser $socialUser): void
    {
        $column = SocialAuth::providerColumn($provider);

        if ($user->{$column}) {
            return;
        }

        $user->forceFill([
            $column => $socialUser->getId(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();
    }

    private function resolveName(SocialiteUser $socialUser): string
    {
        $name = trim((string) $socialUser->getName());

        if ($name !== '') {
            return $name;
        }

        $nickname = trim((string) $socialUser->getNickname());

        if ($nickname !== '') {
            return $nickname;
        }

        return Str::before((string) $socialUser->getEmail(), '@') ?: 'Utilisateur Event Pulse';
    }

    private function organizerProfileIsComplete(User $user): bool
    {
        return $user->isOrganizer()
            && filled($user->phone)
            && $user->organizer_terms_accepted_at !== null;
    }

    private function homeRouteFor(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard', absolute: false);
        }

        if ($user->isOrganizer()) {
            return $user->canAccessOrganizerSpace()
                ? route('organizer.dashboard', absolute: false)
                : route('organizer.pending', absolute: false);
        }

        return route('tickets.index', absolute: false);
    }
}
