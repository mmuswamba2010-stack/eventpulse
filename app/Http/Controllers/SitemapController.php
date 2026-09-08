<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $events = Event::query()
            ->visibleInCatalog()
            ->where('event_date', '>=', now()->subDay())
            ->orderBy('event_date')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', [
                'events' => $events,
                'homeUrl' => route('events.index'),
                'generatedAt' => now(),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
