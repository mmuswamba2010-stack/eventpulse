<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Support\OrganizerTerms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TermsController extends Controller
{
    public function accept(): View|RedirectResponse
    {
        $user = auth()->user();

        if (OrganizerTerms::hasAccepted($user)) {
            return redirect()->route('organizer.dashboard');
        }

        return view('organizer.terms.accept');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'accept_organizer_terms' => ['accepted'],
        ], [], [
            'accept_organizer_terms' => __('Organizer terms acceptance label'),
        ]);

        OrganizerTerms::recordAcceptance($request->user());

        return redirect()
            ->route('organizer.dashboard')
            ->with('success', __('Organizer terms accepted success'));
    }
}
