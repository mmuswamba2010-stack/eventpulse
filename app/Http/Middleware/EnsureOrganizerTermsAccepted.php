<?php

namespace App\Http\Middleware;

use App\Support\OrganizerTerms;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizerTermsAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isOrganizer() || OrganizerTerms::hasAccepted($user)) {
            return $next($request);
        }

        if ($request->routeIs('organizer.terms.*', 'legal.organizer-terms')) {
            return $next($request);
        }

        return redirect()->route('organizer.terms.accept');
    }
}
