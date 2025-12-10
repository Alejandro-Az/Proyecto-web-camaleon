<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\RsvpController;

Route::get('/', function () {
    return view('welcome');
});

// Página pública del evento por slug
Route::get('/eventos/{slug}', [EventController::class, 'show'])->name('events.show');

// Registro/actualización de RSVP de invitados
Route::post('/eventos/{slug}/rsvp', [RsvpController::class, 'store'])->name('events.rsvp.store');
