<?php

use App\Enums\Events\EventCategoryEnum;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Öffentliche Seiten
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('programm/{kategorie?}', [EventController::class, 'index'])
    ->whereIn('kategorie', array_map(fn (EventCategoryEnum $category): string => $category->slug(), EventCategoryEnum::cases()))
    ->name('events.index');
Route::redirect('konzerte', 'programm/konzerte');
Route::redirect('partys', 'programm/partys');
Route::get('event/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('event/{event:slug}/kalender.ics', [EventController::class, 'calendar'])->name('events.calendar');

Route::get('locations', [VenueController::class, 'index'])->name('venues.index');
Route::view('impressum', 'pages.imprint')->name('imprint');
Route::view('datenschutz', 'pages.privacy')->name('privacy');

/*
|--------------------------------------------------------------------------
| Kontakt & Booking – Einsendungen landen im Admin-Posteingang
|--------------------------------------------------------------------------
*/

Route::get('kontakt', [ContactController::class, 'show'])->name('contact');
Route::post('kontakt', [ContactController::class, 'store'])->middleware('throttle:site-forms')->name('contact.store');

/*
|--------------------------------------------------------------------------
| Admin-Bereich
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Bereits gelöschte Veranstaltung (z. B. über die Zurück-Taste aufgerufen): zur Liste statt 404
        $eventMissing = fn () => to_route('admin.events.index')->with('error', 'Diese Veranstaltung gibt es nicht mehr – sie wurde vermutlich gelöscht.');

        Route::resource('events', Admin\EventController::class)->except('show')->missing($eventMissing);
        Route::post('events/{event}/duplizieren', [Admin\EventController::class, 'duplicate'])->name('events.duplicate')->missing($eventMissing);
        Route::post('events/{event}/status', [Admin\EventController::class, 'toggleStatus'])->name('events.status')->missing($eventMissing);

        Route::get('nachrichten', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('nachrichten/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
        Route::put('nachrichten/{message}', [Admin\MessageController::class, 'update'])->name('messages.update');
        Route::delete('nachrichten/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');
        Route::post('nachrichten/{message}/ungelesen', [Admin\MessageController::class, 'markUnread'])->name('messages.unread');
        Route::get('nachrichten/{message}/anhang/{media}', [Admin\MessageController::class, 'attachment'])->whereNumber('media')->name('messages.attachment');
        Route::post('nachrichten/{message}/antworten', [Admin\MessageReplyController::class, 'store'])->name('messages.replies.store');

        Route::resource('locations', Admin\VenueController::class)
            ->except('show')
            ->parameters(['locations' => 'venue'])
            ->names('venues');

        Route::resource('veranstalter', Admin\OrganizerController::class)
            ->except('show')
            ->parameters(['veranstalter' => 'organizer'])
            ->names('organizers');

        Route::get('seiten/{page}', [Admin\PageController::class, 'edit'])->name('pages.edit');
        Route::put('seiten/{page}', [Admin\PageController::class, 'update'])->name('pages.update');

        Route::get('benutzer', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('benutzer', [Admin\UserController::class, 'store'])->name('users.store');
        Route::delete('benutzer/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

        Route::get('konto', [Admin\AccountController::class, 'edit'])->name('account.edit');
        Route::put('konto', [Admin\AccountController::class, 'update'])->name('account.update');
    });
});
