<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterBroadcast;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Support\AdminInsights;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function index(): View
    {
        $subscribers = NewsletterSubscriber::active()
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.newsletter.index', [
            'subscribers' => $subscribers,
            'activeCount' => NewsletterSubscriber::active()->count(),
            'recentEvents' => AdminInsights::recentPublishedEvents(),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'intro' => ['nullable', 'string', 'max:1000'],
            'days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $events = AdminInsights::recentPublishedEvents($validated['days']);

        if ($events->isEmpty()) {
            return back()->withErrors(['days' => 'Aucun événement publié sur cette période.']);
        }

        $intro = filled($validated['intro'] ?? null)
            ? $validated['intro']
            : 'Voici les derniers événements publiés sur Event Pulse :';

        $subscribers = NewsletterSubscriber::active()->get();
        $sent = 0;

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)->queue(new NewsletterBroadcast(
                intro: $intro,
                events: $events,
                unsubscribeUrl: route('newsletter.unsubscribe', $subscriber->unsubscribe_token),
            ));
            $sent++;
        }

        return back()->with('admin_success', "Newsletter envoyée à {$sent} inscrit(s).");
    }

    public function export(): StreamedResponse
    {
        $filename = 'newsletter-subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'subscribed_at']);

            NewsletterSubscriber::active()
                ->orderBy('email')
                ->each(function (NewsletterSubscriber $subscriber) use ($handle) {
                    fputcsv($handle, [$subscriber->email, $subscriber->created_at?->toDateTimeString()]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
