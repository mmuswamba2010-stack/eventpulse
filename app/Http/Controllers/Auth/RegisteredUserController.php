<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:organizer,participant'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $welcome = $this->welcomeMessage($user);

        if ($user->isOrganizer()) {
            return redirect(route('organizer.dashboard', absolute: false))
                ->with('success', $welcome);
        }

        return redirect(route('events.index', absolute: false))
            ->with('success', $welcome);
    }

    private function welcomeMessage(User $user): string
    {
        $firstName = Str::before(trim($user->name), ' ') ?: $user->name;

        if ($user->isOrganizer()) {
            return "Bienvenue, {$firstName} ! Votre espace organisateur est prêt — publiez votre premier événement.";
        }

        return "Bienvenue, {$firstName} ! Votre compte est prêt — découvrez les événements et réservez vos places.";
    }
}
