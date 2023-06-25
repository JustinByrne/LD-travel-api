<?php

use App\Http\Controllers\Api\V1\Admin\TourController as AdminTourController;
use App\Http\Controllers\Api\V1\Admin\TravelController as AdminTravelController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use Illuminate\Support\Facades\Route;

Route::get('/travels', [TravelController::class, 'index'])->name('travels');
Route::get('/travels/{travel:slug}/tours', [TourController::class, 'index'])->name('tours');

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth:sanctum',
        'role:admin',
    ])
    ->group(function () {
        Route::post('/travels', [AdminTravelController::class, 'store'])->name('travels.store');
        Route::post('/travels/{travel:slug}/tours', [AdminTourController::class, 'store'])->name('tours.store');
    });

Route::post('login', LoginController::class)->name('login');
