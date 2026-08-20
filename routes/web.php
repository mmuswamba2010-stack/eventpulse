<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NewsletterUnsubscribeController;
use App\Http\Controllers\Organizer\DashboardController;
use App\Http\Controllers\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\Organizer\PendingController;
use App\Http\Controllers\Organizer\ScanController;
use App\Http\Controllers\Organizer\SuspendedController;
use App\Http\Controllers\PaymentStatusController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Webhooks\MobileMoneyWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Invité
|--------------------------------------------------------------------------
*/
Route::get('/', [EventController::class, 'index'])->name('events.index');
Route::get('/events/grid', [EventController::class, 'grid'])->name('events.grid');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::post('/newsletter', [NewsletterController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('newsletter.subscribe');

Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');

Route::get('/newsletter/unsubscribe/{token}', [NewsletterUnsubscribeController::class, 'show'])
    ->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe/{token}', [NewsletterUnsubscribeController::class, 'destroy'])
    ->name('newsletter.unsubscribe.confirm');

Route::post('/webhooks/mobile-money', MobileMoneyWebhookController::class)
    ->middleware('webhook.mm')
    ->name('webhooks.mobile-money');

Route::get('/internal/cron/schedule/{token}', \App\Http\Controllers\Internal\CronScheduleController::class)
    ->name('internal.cron.schedule');

// Redirection du "dashboard" Breeze selon le rôle.
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return $user->isOrganizer()
        ? ($user->canAccessOrganizerSpace()
            ? redirect()->route('organizer.dashboard')
            : redirect()->route('organizer.pending'))
        : redirect()->route('tickets.index');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authentifié (Participant)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/events/{event}/book', [TicketController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('tickets.store');
    Route::get('/my-tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/my-tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/my-tickets/{ticket}/download', [TicketController::class, 'downloadPdf'])->name('tickets.download');
    Route::get('/payments/{payment}', [PaymentStatusController::class, 'show'])->name('payments.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Authentifié (Organisateur)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'organizer'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/pending', PendingController::class)->name('pending');
    Route::get('/suspended', SuspendedController::class)->name('suspended');

    Route::middleware('organizer.approved')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/events', [OrganizerEventController::class, 'index'])->name('events.index');
        Route::get('/events/create', [OrganizerEventController::class, 'create'])->name('events.create');
        Route::post('/events', [OrganizerEventController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('events.store');
        Route::get('/events/{event}/edit', [OrganizerEventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [OrganizerEventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [OrganizerEventController::class, 'destroy'])->name('events.destroy');
        Route::get('/events/{event}/pay', [OrganizerEventController::class, 'pay'])->name('events.pay');
        Route::post('/events/{event}/pay', [OrganizerEventController::class, 'processPayment'])->name('events.pay.process');

        Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
        Route::post('/scan/validate', [ScanController::class, 'validateTicket'])
            ->middleware('throttle:60,1')
            ->name('scan.validate');
    });
});

/*
|--------------------------------------------------------------------------
| Administration Event Pulse
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
    Route::post('/newsletter/send', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])
        ->middleware('throttle:3,1')
        ->name('newsletter.send');
    Route::get('/newsletter/export', [\App\Http\Controllers\Admin\NewsletterController::class, 'export'])->name('newsletter.export');
    Route::get('/organizers', [\App\Http\Controllers\Admin\OrganizerController::class, 'index'])->name('organizers.index');
    Route::patch('/organizers/{organizer}/approve', [\App\Http\Controllers\Admin\OrganizerController::class, 'approve'])->name('organizers.approve');
    Route::patch('/organizers/{organizer}/reject', [\App\Http\Controllers\Admin\OrganizerController::class, 'reject'])->name('organizers.reject');
    Route::patch('/organizers/{organizer}/suspend', [\App\Http\Controllers\Admin\OrganizerController::class, 'suspend'])->name('organizers.suspend');
    Route::patch('/organizers/{organizer}/unsuspend', [\App\Http\Controllers\Admin\OrganizerController::class, 'unsuspend'])->name('organizers.unsuspend');
    Route::delete('/organizers/{organizer}', [\App\Http\Controllers\Admin\OrganizerController::class, 'destroy'])->name('organizers.destroy');
    Route::get('/participants', [\App\Http\Controllers\Admin\ParticipantController::class, 'index'])->name('participants.index');
});

require __DIR__.'/auth.php';
