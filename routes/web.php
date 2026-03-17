<?php

use App\Http\Controllers\ActorController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/actors/{actor}', [ActorController::class, 'show'])->name('actors.show');
Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
        Route::get('/movies/create', [MovieController::class, 'create'])->name('movies.create');
        Route::post('/movies', [MovieController::class, 'store'])->name('movies.store');
        Route::get('/movies/{movie}/edit', [MovieController::class, 'edit'])->name('movies.edit');
        Route::patch('/movies/{movie}', [MovieController::class, 'update'])->name('movies.update');
        Route::delete('/movies/{movie}', [MovieController::class, 'destroy'])->name('movies.destroy');

        Route::get('/actors', [ActorController::class, 'index'])->name('actors.index');
        Route::get('/actors/create', [ActorController::class, 'create'])->name('actors.create');
        Route::post('/actors', [ActorController::class, 'store'])->name('actors.store');
        Route::get('/actors/{actor}/edit', [ActorController::class, 'edit'])->name('actors.edit');
        Route::patch('/actors/{actor}', [ActorController::class, 'update'])->name('actors.update');
        Route::delete('/actors/{actor}', [ActorController::class, 'destroy'])->name('actors.destroy');

        Route::get('/people', [PersonController::class, 'index'])->name('people.index');
        Route::get('/people/create', [PersonController::class, 'create'])->name('people.create');
        Route::post('/people', [PersonController::class, 'store'])->name('people.store');
        Route::get('/people/{person}/edit', [PersonController::class, 'edit'])->name('people.edit');
        Route::patch('/people/{person}', [PersonController::class, 'update'])->name('people.update');
        Route::delete('/people/{person}', [PersonController::class, 'destroy'])->name('people.destroy');
    });
});

require __DIR__.'/auth.php';
