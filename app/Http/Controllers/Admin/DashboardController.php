<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminInsights;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => AdminInsights::stats(),
            'suspiciousParticipants' => AdminInsights::suspiciousParticipants(),
            'flaggedOrganizers' => AdminInsights::flaggedOrganizers(),
            'recentEvents' => AdminInsights::recentPublishedEvents(),
        ]);
    }
}
