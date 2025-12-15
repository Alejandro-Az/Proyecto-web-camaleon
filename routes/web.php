<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\RsvpController;
use App\Http\Controllers\Public\SongController;
use App\Http\Controllers\Public\GuestPhotoController;

require base_path('routes/modules/gifts_public.php');
require base_path('routes/modules/gifts_admin.php');

Route::get('/', function () {
    return view('welcome');
});

// Página pública del evento por slug
Route::get('/eventos/{slug}', [EventController::class, 'show'])
    ->name('events.show');

// Registro/actualización de RSVP de invitados
Route::post('/eventos/{slug}/rsvp', [RsvpController::class, 'store'])
    ->name('events.rsvp.store');

// Sugerencia de canciones
Route::post('/eventos/{slug}/canciones', [SongController::class, 'store'])
    ->name('events.songs.store');

// Votos a canciones
Route::post('/eventos/{slug}/canciones/{song}/votar', [SongController::class, 'vote'])
    ->name('events.songs.vote');

// Subida de fotos por invitados
Route::post('/eventos/{slug}/fotos-invitados', [GuestPhotoController::class, 'store'])
    ->name('events.guest-photos.store');
