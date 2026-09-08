<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow:',
            'Disallow: /admin',
            'Disallow: /organizer',
            'Disallow: /profile',
            'Disallow: /my-tickets',
            'Disallow: /payments',
            'Disallow: /dashboard',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
