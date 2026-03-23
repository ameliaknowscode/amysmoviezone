<?php

use App\Http\Controllers\MovieBrowseController;
use App\Http\Controllers\MovieBySlugController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\MovieCreditImportController;
use App\Http\Controllers\MovieImportController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PersonCreditImportController;
use App\Http\Controllers\PersonTypeCreditsController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $movieCount  = \App\Models\Movie::count();
    $peopleCount = \App\Models\Person::count();
    $creditCount = \App\Models\Credit::count();

    $recentMovies = \App\Models\Movie::latest()->limit(12)->get();

    $recentRatings = \App\Models\Rating::with('movie', 'user')
        ->whereHas('user', fn ($q) => $q->where('ratings_private', false))
        ->latest()
        ->limit(50)
        ->get()
        ->unique('movie_id')
        ->take(8);

    $followingRatings = collect();
    if (auth()->check()) {
        $followingIds = auth()->user()->following()->pluck('users.id');
        if ($followingIds->isNotEmpty()) {
            $followingRatings = \App\Models\Rating::with('movie', 'user')
                ->whereIn('user_id', $followingIds)
                ->whereHas('user', fn ($q) => $q->where('ratings_private', false))
                ->latest()
                ->limit(50)
                ->get()
                ->unique('movie_id')
                ->take(8);
        }
    }

    return view('welcome', compact('movieCount', 'peopleCount', 'creditCount', 'recentMovies', 'recentRatings', 'followingRatings'));
})->name('home');

Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::get('/movies', MovieBrowseController::class)->name('movies.browse');
Route::get('/director-connections', [SearchController::class, 'directorConnections'])->name('director-connections');
Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');
Route::get('/u/{username}', [UserProfileController::class, 'show'])->name('profile.show');
Route::get('/u/{username}/watchlist', [UserProfileController::class, 'watchlist'])->name('profile.watchlist');
Route::get('/u/{username}/followers', [UserProfileController::class, 'followers'])->name('profile.followers');
Route::get('/u/{username}/following', [UserProfileController::class, 'following'])->name('profile.following');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/u/{username}/follow', [FollowController::class, 'store'])->name('follow.store');
    Route::delete('/u/{username}/follow', [FollowController::class, 'destroy'])->name('follow.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/movies/{movie}/rate', [RatingController::class, 'store'])->name('movies.rate');
    Route::delete('/movies/{movie}/rating', [RatingController::class, 'destroy'])->name('movies.rating.destroy');

    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/movies/{movie}/watchlist', [WatchlistController::class, 'store'])->name('movies.watchlist.store');
    Route::delete('/movies/{movie}/watchlist', [WatchlistController::class, 'destroy'])->name('movies.watchlist.destroy');
    Route::patch('/watchlist/privacy', [WatchlistController::class, 'updatePrivacy'])->name('watchlist.privacy');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
        Route::get('/movies/import', [MovieImportController::class, 'create'])->name('movies.import');
        Route::post('/movies/import', [MovieImportController::class, 'store'])->name('movies.import.store');
        Route::get('/movies/create', [MovieController::class, 'create'])->name('movies.create');
        Route::post('/movies', [MovieController::class, 'store'])->name('movies.store');
        Route::get('/movies/{movie}/credits/import', [MovieCreditImportController::class, 'create'])->name('movies.credits.import');
        Route::post('/movies/{movie}/credits/import', [MovieCreditImportController::class, 'store'])->name('movies.credits.import.store');
        Route::get('/movies/{movie}/edit', [MovieController::class, 'edit'])->name('movies.edit');
        Route::patch('/movies/{movie}', [MovieController::class, 'update'])->name('movies.update');
        Route::delete('/movies/{movie}', [MovieController::class, 'destroy'])->name('movies.destroy');

        Route::get('/people', [PersonController::class, 'index'])->name('people.index');
        Route::get('/people/create', [PersonController::class, 'create'])->name('people.create');
        Route::post('/people', [PersonController::class, 'store'])->name('people.store');
        Route::get('/people/{person}/edit', [PersonController::class, 'edit'])->name('people.edit');
        Route::patch('/people/{person}', [PersonController::class, 'update'])->name('people.update');
        Route::delete('/people/{person}', [PersonController::class, 'destroy'])->name('people.destroy');
        Route::get('/people/{person}/credits/import', [PersonCreditImportController::class, 'create'])->name('people.credits.import');
        Route::post('/people/{person}/credits/import', [PersonCreditImportController::class, 'store'])->name('people.credits.import.store');

        Route::get('/types', [TypeController::class, 'index'])->name('types.index');
        Route::get('/types/create', [TypeController::class, 'create'])->name('types.create');
        Route::post('/types', [TypeController::class, 'store'])->name('types.store');
        Route::get('/types/{type}/edit', [TypeController::class, 'edit'])->name('types.edit');
        Route::patch('/types/{type}', [TypeController::class, 'update'])->name('types.update');
        Route::delete('/types/{type}', [TypeController::class, 'destroy'])->name('types.destroy');
    });
});

require __DIR__.'/auth.php';

// Wildcards — must be last so they cannot shadow any specific route above.
Route::get('/movie/{movieSlug}', MovieBySlugController::class)->name('movies.public');
Route::get('/{typeSlug}/{personSlug}', PersonTypeCreditsController::class)->name('credits.by-type');
