<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PublicRoomReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AgendaController::class, 'index'])->name('landing');

// ── API PUBLIK untuk FullCalendar & AJAX ──────────────────────────────────
//
//  Middleware:
//  - throttle:api-publik → named rate limiter di AppServiceProvider
//    max 60 request/menit per IP, return HTTP 429 jika melebihi  [SECURITY]
//  - Tidak perlu auth (publik), scope 'published' di Controller
//    memastikan data privat tidak pernah keluar                   [SECURITY]
//
Route::prefix('api/agenda')
    ->middleware(['throttle:api-publik'])
    ->group(function () {

        Route::get('/kalender', [AgendaController::class, 'kalenderFeed'])
            ->name('api.agenda.kalender');

        Route::get('/polling', [AgendaController::class, 'polling'])
            ->name('api.agenda.polling');

        Route::get('/{id}', [AgendaController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('api.agenda.show');
    });

Route::get('/reservasi-ruangan', [PublicRoomReservationController::class, 'index'])
    ->name('room-reservation.index');

Route::post('/reservasi-ruangan', [PublicRoomReservationController::class, 'store'])
    ->middleware('throttle:reservasi-publik')
    ->name('room-reservation.store');

Route::get('/api/room-reservation/kalender', [PublicRoomReservationController::class, 'kalenderFeed'])
    ->middleware('throttle:api-publik')
    ->name('api.room-reservation.kalender');
