<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin;

Route::middleware('auth.basic')->group(function () {
    // Rotas comum
    Route::get('/', [MovieController::class, 'index'])->name('movies.index');
    Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
    Route::get('/my-rentals', [RentalController::class, 'index'])->name('rentals.index');
    Route::post('/movies/{movie}/rent', [RentalController::class, 'store'])->name('rentals.store');
    Route::post('/rentals/{rental}/cancel', [RentalController::class, 'cancel'])->name('rentals.cancel');
    Route::post('/rentals/{rental}/review', [ReviewController::class, 'store'])->name('reviews.store');

    // Rotas admin
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('genres', Admin\GenreController::class)->except('show');
        Route::resource('movies', Admin\MovieController::class)->except('show');
        Route::get('rentals', [Admin\RentalController::class, 'index'])->name('rentals.index');
        Route::post('rentals/{rental}/pickup', [Admin\RentalController::class, 'pickup'])->name('rentals.pickup');
        Route::post('rentals/{rental}/return', [Admin\RentalController::class, 'return'])->name('rentals.return');
        // TMDB
        Route::get('tmdb/search', [Admin\TmdbController::class, 'search'])->name('tmdb.search');
        Route::get('tmdb/movies/{id}', [Admin\TmdbController::class, 'show'])->name('tmdb.show');
    });
});
