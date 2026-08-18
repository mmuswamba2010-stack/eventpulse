<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [], [
            'email' => 'adresse e-mail',
        ]);

        $email = Str::lower(trim($validated['email']));

        $subscriber = NewsletterSubscriber::firstOrCreate(['email' => $email]);

        if (! $subscriber->wasRecentlyCreated) {
            return redirect()
                ->back()
                ->withFragment('about')
                ->with('newsletter_info', 'Cet e-mail est déjà inscrit. Vous recevrez nos prochains événements !');
        }

        return redirect()
            ->back()
            ->withFragment('about')
            ->with('newsletter_success', 'Merci ! Votre e-mail a bien été enregistré.');
    }
}
