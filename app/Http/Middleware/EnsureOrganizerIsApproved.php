<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizerIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isOrganizer()) {
            return $next($request);
        }

        if ($user->isSuspended()) {
            if ($request->routeIs('organizer.suspended')) {
                return $next($request);
            }

            return redirect()->route('organizer.suspended');
        }

        if ($user->needsOrganizerApproval()) {
            if ($request->routeIs('organizer.pending')) {
                return $next($request);
            }

            return redirect()->route('organizer.pending');
        }

        if ($user->organizer_status === 'rejected') {
            if ($request->routeIs('organizer.pending')) {
                return $next($request);
            }

            return redirect()->route('organizer.pending');
        }

        return $next($request);
    }
}
