<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\EventController;

Route::get('/', function () {
    return view('welcome');
});

// Página pública del evento por slug
Route::get('/eventos/{slug}', [EventController::class, 'show'])
    ->name('events.show');
