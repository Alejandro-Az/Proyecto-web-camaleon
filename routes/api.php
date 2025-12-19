<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Admin\EventPhotoController;

/*
|--------------------------------------------------------------------------
| API Auth (JWT)
|--------------------------------------------------------------------------
| Endpoints para obtener token y consultar usuario actual.
*/
Route::prefix('auth')
    ->name('auth.')
    ->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');

        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        });
    });

/*
|--------------------------------------------------------------------------
| Admin (JWT + role)
|--------------------------------------------------------------------------
| Protegemos endpoints "admin" con token JWT y rol admin.
*/
Route::middleware(['auth:api', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('events/{event}/photos', [EventPhotoController::class, 'store'])
            ->name('events.photos.store');
    });