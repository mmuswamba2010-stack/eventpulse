<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\Organizer\DashboardController;
use App\Http\Controllers\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\Organizer\ScanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
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

// Redirection du "dashboard" Breeze selon le rôle.
Route::get('/dashboard', function () {
    return auth()->user()->isOrganizer()
        ? redirect()->route('organizer.dashboard')
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

require __DIR__.'/auth.php';
