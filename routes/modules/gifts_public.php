<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\GiftController;

Route::prefix('eventos/{slug}/regalos')
    ->name('events.gifts.')
    ->group(function () {

        // Rehidrata UI (cantidades, mi reserva y claimers opcional)
        Route::get('resumen', [GiftController::class, 'summary'])
            ->middleware('throttle:60,1')
            ->name('summary');

        // Compatibilidad (si su JS viejo todavía lo usa)
        Route::get('mis-reservas', [GiftController::class, 'myClaims'])
            ->middleware('throttle:60,1')
            ->name('myClaims');

        // Acciones críticas: más estrictas por seguridad
        Route::post('{gift}/reservar', [GiftController::class, 'reserve'])
            ->middleware('throttle:20,1')
            ->name('reserve');

        Route::post('{gift}/liberar', [GiftController::class, 'unreserve'])
            ->middleware('throttle:20,1')
            ->name('unreserve');
    });
