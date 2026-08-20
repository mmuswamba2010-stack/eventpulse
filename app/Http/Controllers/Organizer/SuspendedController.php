<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SuspendedController extends Controller
{
    public function __invoke(): View
    {
        return view('organizer.suspended');
    }
}
