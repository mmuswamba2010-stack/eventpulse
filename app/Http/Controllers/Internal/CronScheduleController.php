<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class CronScheduleController extends Controller
{
    public function __invoke(string $token): Response
    {
        $expected = config('eventpulse.cron_token');

        if (blank($expected) || ! hash_equals($expected, $token)) {
            abort(404);
        }

        Artisan::call('schedule:run');

        return response(Artisan::output(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
