<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\OrganizerTerms;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function createParticipant(): View
    {
        return view('auth.register-participant');
    }

    public function createOrganizer(): View
    {
        return view('auth.register-organizer');
    }

    public function storeParticipant(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user = $this->createUser($request, role: 'participant');

        return $this->redirectAfterRegistration($user, defaultRoute: 'events.index');
    }

    public function storeOrganizer(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'phone' => ['required', 'string', 'min:8', 'max:30'],
            'accept_organizer_terms' => ['accepted'],
        ], [], [
            'accept_organizer_terms' => __('Organizer terms acceptance label'),
        ]);

        $user = $this->createUser($request, role: 'organizer', phone: $request->phone);

        return $this->redirectAfterRegistration($user, defaultRoute: 'organizer.dashboard');
    }

    /**
     * @deprecated Conservé pour compatibilité — préférer register.participant / register.organizer.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:organizer,participant'],
        ]);

        return $request->role === 'organizer'
            ? $this->storeOrganizer($request)
            : $this->storeParticipant($request);
    }

    private function createUser(Request $request, string $role, ?string $phone = null): User
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $role,
            'phone' => $phone,
            'organizer_status' => $role === 'organizer'
                ? (config('eventpulse.organizer_moderation', true) ? 'pending' : 'approved')
                : null,
            'organizer_terms_accepted_at' => $role === 'organizer' ? now() : null,
            'organizer_terms_version' => $role === 'organizer' ? OrganizerTerms::version() : null,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return $user;
    }

    private function redirectAfterRegistration(User $user, string $defaultRoute): RedirectResponse
    {
        $welcome = $this->welcomeMessage($user);

        if ($user->isOrganizer()) {
            $route = $user->canAccessOrganizerSpace()
                ? route('organizer.dashboard', absolute: false)
                : route('organizer.pending', absolute: false);

            return redirect()->intended($route)->with('success', $welcome);
        }

        return redirect()->intended(route($defaultRoute, absolute: false))
            ->with('success', $welcome);
    }

    private function welcomeMessage(User $user): string
    {
        $firstName = Str::before(trim($user->name), ' ') ?: $user->name;

        if ($user->isOrganizer()) {
            return __('Account created welcome organizer', ['name' => $firstName]);
        }

        return __('Account created welcome participant', ['name' => $firstName]);
    }
}
