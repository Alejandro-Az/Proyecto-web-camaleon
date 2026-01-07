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
    });
});
