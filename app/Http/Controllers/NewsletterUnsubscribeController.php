<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsletterUnsubscribeController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->firstOrFail();

        if ($subscriber->isUnsubscribed() || session('unsubscribed')) {
            return view('newsletter.unsubscribed', ['already' => true]);
        }

        return view('newsletter.unsubscribe', compact('subscriber'));
    }

    public function destroy(string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->firstOrFail();

        $subscriber->update(['unsubscribed_at' => now()]);

        return redirect()
            ->route('newsletter.unsubscribe', $token)
            ->with('unsubscribed', true);
    }
}
