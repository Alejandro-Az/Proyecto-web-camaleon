<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\EventController;

Route::prefix('panel')->name('client.')->group(function () {
    Route::middleware('guest:client')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.store');
    });

    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth:client')
        ->name('logout');

    Route::middleware(['auth:client', 'client.role'])->group(function () {
        Route::get('/', fn () => redirect('/panel/eventos'))->name('home');

        Route::get('eventos', [EventController::class, 'index'])->name('events.index');
        Route::get('eventos/crear', [EventController::class, 'create'])->name('events.create');
        Route::post('eventos', [EventController::class, 'store'])->name('events.store');
        
        Route::get('eventos/{eventId}', [EventController::class, 'show'])->name('events.show');
        Route::put('eventos/{eventId}', [EventController::class, 'update'])->name('events.update');
    // Event Hero Image
    Route::post('eventos/{event}/hero', [EventController::class, 'updateHero'])->name('events.hero');

    // Guest Management (Epic 2)
    Route::get('eventos/{event}/invitados', [\App\Http\Controllers\Client\GuestController::class, 'index'])->name('events.guests.index');
    Route::post('eventos/{event}/invitados', [\App\Http\Controllers\Client\GuestController::class, 'store'])->name('events.guests.store');
    Route::put('invitados/{guest}', [\App\Http\Controllers\Client\GuestController::class, 'update'])->name('guests.update');
    Route::delete('invitados/{guest}', [\App\Http\Controllers\Client\GuestController::class, 'destroy'])->name('guests.destroy');

    // Content Management (Epic 3)
    Route::get('eventos/{event}/contenido', [\App\Http\Controllers\Client\ContentController::class, 'index'])->name('events.content.index');

    Route::resource('eventos.dress-codes', \App\Http\Controllers\Client\DressCodeController::class)
        ->shallow()
        ->except(['index', 'show', 'create', 'edit']);

    Route::resource('eventos.phrases', \App\Http\Controllers\Client\RomanticPhraseController::class)
        ->shallow()
        ->except(['index', 'show', 'create', 'edit']);

    Route::resource('eventos.stories', \App\Http\Controllers\Client\StoryController::class)
        ->shallow()
        ->except(['index', 'show', 'create', 'edit']);

    Route::resource('eventos.schedules', \App\Http\Controllers\Client\ScheduleController::class)
        ->shallow()
        ->except(['index', 'show', 'create', 'edit']);

    Route::resource('eventos.locations', \App\Http\Controllers\Client\LocationController::class)
        ->shallow()
        ->except(['index', 'show', 'create', 'edit']);

    Route::resource('eventos.gifts', \App\Http\Controllers\Client\GiftController::class)
        ->shallow()
        ->except(['index', 'show', 'create', 'edit']);

    });
});
