<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin;

Route::middleware('auth.basic')->group(function () {
    // Rotas acessíveis por qualquer utilizador autenticado (comum ou admin)
    Route::get('/', [MovieController::class, 'index'])->name('movies.index');
    Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
    Route::get('/my-rentals', [RentalController::class, 'index'])->name('rentals.index');
    Route::post('/movies/{movie}/rent', [RentalController::class, 'store'])->name('rentals.store');
    Route::post('/rentals/{rental}/review', [ReviewController::class, 'store'])->name('reviews.store');

    // Rotas exclusivas de Administrador
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('genres/import', [Admin\GenreController::class, 'import'])->name('genres.import');
        Route::resource('genres', Admin\GenreController::class)->except('show');
        Route::resource('movies', Admin\MovieController::class)->except('show');
        Route::get('rentals', [Admin\RentalController::class, 'index'])->name('rentals.index');
        Route::post('rentals/{rental}/return', [Admin\RentalController::class, 'return'])->name('rentals.return');
        Route::get('tmdb/search', [Admin\TmdbController::class, 'search'])->name('tmdb.search');
    });
});
