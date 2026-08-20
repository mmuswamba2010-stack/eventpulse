<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminInsights;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function index(): View
    {
        $participants = User::query()
            ->where('role', 'participant')
            ->withCount([
                'tickets as tickets_count' => fn ($q) => $q->where('status', '!=', 'cancelled'),
                'tickets as cancelled_tickets_count' => fn ($q) => $q->where('status', 'cancelled'),
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        $suspicious = AdminInsights::suspiciousParticipants()
            ->keyBy('id');

        return view('admin.participants.index', compact('participants', 'suspicious'));
    }
}
