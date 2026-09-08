<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function organizerTerms(): View
    {
        return view('legal.organizer-terms');
    }
}
