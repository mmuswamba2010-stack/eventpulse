<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $firstName = Str::before(trim($user->name), ' ') ?: $user->name;

        $home = match (true) {
            $user->isAdmin() => route('admin.dashboard', absolute: false),
            $user->isOrganizer() => $user->canAccessOrganizerSpace()
                ? route('organizer.dashboard', absolute: false)
                : route('organizer.pending', absolute: false),
            default => route('tickets.index', absolute: false),
        };

        return redirect()
            ->intended($home)
            ->with('success', __('Login welcome', ['name' => $firstName]));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
