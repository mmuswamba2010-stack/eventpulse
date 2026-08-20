<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMobileMoneyWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('eventpulse.webhook_secret');

        if (blank($secret)) {
            abort(503, 'Webhook not configured.');
        }

        $signature = $request->header('X-EventPulse-Signature')
            ?? $request->header('X-Signature');

        if (blank($signature)) {
            abort(401, 'Missing signature.');
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            abort(401, 'Invalid signature.');
        }

        return $next($request);
    }
}
