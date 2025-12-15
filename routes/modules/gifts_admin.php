<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EventGiftAdminController;

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('eventos/{event}/regalos/{gift}/marcar-comprado', [EventGiftAdminController::class, 'markPurchased'])
            ->name('events.gifts.markPurchased');
    });
