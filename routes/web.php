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
use App\Http\Controllers\GenreController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AdminCreditImportController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\MovieListController;
use App\Http\Controllers\MovieListFollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\MovieListItemController;
use App\Http\Controllers\RecommendationsController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DirectorComparisonController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GenreImportController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $movieCount  = \App\Models\Movie::count();
    $peopleCount = \App\Models\Person::count();
    $creditCount = \App\Models\Credit::count();
    $memberCount = \App\Models\User::count();

    $recentMovies = \App\Models\Movie::latest()->limit(12)->get();

    $recentRatings = \App\Models\Rating::with('movie', 'user')
        ->whereIn('id', function ($sub) {
            $sub->select(DB::raw('MAX(ratings.id)'))
                ->from('ratings')
                ->join('users', 'users.id', '=', 'ratings.user_id')
                ->where('users.profile_private', false)
                ->groupBy('ratings.movie_id');
        })
        ->latest()
        ->limit(8)
        ->get();

    $recentReviews = \App\Models\Review::with('movie', 'user')
        ->whereNotNull('body')
        ->whereHas('user', fn ($q) => $q->where('profile_private', false))
        ->latest()
        ->limit(5)
        ->get();

    $followingRatings = collect();
    if (auth()->check()) {
        $followingIds = auth()->user()->following()->pluck('users.id');
        if ($followingIds->isNotEmpty()) {
            $followingRatings = \App\Models\Rating::with('movie', 'user')
                ->whereIn('id', function ($sub) use ($followingIds) {
                    $sub->select(DB::raw('MAX(ratings.id)'))
                        ->from('ratings')
                        ->join('users', 'users.id', '=', 'ratings.user_id')
                        ->where('users.profile_private', false)
                        ->whereIn('ratings.user_id', $followingIds)
                        ->groupBy('ratings.movie_id');
                })
                ->latest()
                ->limit(8)
                ->get();
        }
    }

    return view('welcome', compact('movieCount', 'peopleCount', 'creditCount', 'memberCount', 'recentMovies', 'recentRatings', 'followingRatings', 'recentReviews'));
})->name('home');

Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::get('/movies', MovieBrowseController::class)->name('movies.browse');
Route::get('/director-connections', [SearchController::class, 'directorConnections'])->name('director-connections');
Route::get('/directors/search', [PersonController::class, 'searchDirectors'])->name('directors.search');
Route::get('/compare', [DirectorComparisonController::class, 'index'])->name('compare.index');
Route::get('/compare/{personA}/{personB}', [DirectorComparisonController::class, 'show'])->name('compare.show');
Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');
Route::get('/u/{username}', [UserProfileController::class, 'show'])->name('profile.show');
Route::get('/u/{username}/lists', [UserProfileController::class, 'lists'])->name('profile.lists');
Route::get('/u/{username}/diary', [UserProfileController::class, 'diary'])->name('profile.diary');
Route::get('/u/{username}/watchlist', [UserProfileController::class, 'watchlist'])->name('profile.watchlist');
Route::get('/u/{username}/followers', [UserProfileController::class, 'followers'])->name('profile.followers');
Route::get('/u/{username}/following', [UserProfileController::class, 'following'])->name('profile.following');

Route::get('/onboarding', [OnboardingController::class, 'show'])->middleware(['auth', 'verified'])->name('onboarding');
Route::post('/onboarding', [OnboardingController::class, 'complete'])->middleware(['auth', 'verified'])->name('onboarding.complete');

Route::get('/dashboard', function () {
    if (auth()->user()->is_admin) {
        return redirect()->route('admin.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::post('/reviews/{review}/likes', [ReviewLikeController::class, 'store'])->name('reviews.likes.store');
    Route::delete('/reviews/{review}/likes', [ReviewLikeController::class, 'destroy'])->name('reviews.likes.destroy');

    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::get('/feed/more', [FeedController::class, 'more'])->name('feed.more');
    Route::get('/recommendations', [RecommendationsController::class, 'index'])->name('recommendations');

    Route::get('/lists', [MovieListController::class, 'index'])->name('lists.index');
    Route::get('/lists/create', [MovieListController::class, 'create'])->name('lists.create');
    Route::post('/lists', [MovieListController::class, 'store'])->name('lists.store');
    Route::get('/lists/{movieList}', [MovieListController::class, 'show'])->name('lists.show');
    Route::get('/lists/{movieList}/edit', [MovieListController::class, 'edit'])->name('lists.edit');
    Route::put('/lists/{movieList}', [MovieListController::class, 'update'])->name('lists.update');
    Route::delete('/lists/{movieList}', [MovieListController::class, 'destroy'])->name('lists.destroy');
    Route::post('/lists/{movieList}/movies', [MovieListItemController::class, 'store'])->name('lists.movies.store');
    Route::delete('/lists/{movieList}/movies/{movie}', [MovieListItemController::class, 'destroy'])->name('lists.movies.destroy');
    Route::post('/lists/{movieList}/reorder', [MovieListItemController::class, 'reorder'])->name('lists.movies.reorder');
    Route::post('/lists/{movieList}/follow', [MovieListFollowController::class, 'store'])->name('lists.follow');
    Route::delete('/lists/{movieList}/follow', [MovieListFollowController::class, 'destroy'])->name('lists.unfollow');

    Route::post('/u/{username}/follow', [FollowController::class, 'store'])->name('follow.store');
    Route::delete('/u/{username}/follow', [FollowController::class, 'destroy'])->name('follow.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/movies/{movie}/rate', [RatingController::class, 'store'])->name('movies.rate');
    Route::delete('/movies/{movie}/rating', [RatingController::class, 'destroy'])->name('movies.rating.destroy');

    Route::post('/movies/{movie}/review', [ReviewController::class, 'store'])->name('movies.review.store');
    Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/stats', [StatsController::class, 'show'])->name('stats.show');
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/movies/{movie}/watchlist', [WatchlistController::class, 'store'])->name('movies.watchlist.store');
    Route::patch('/movies/{movie}/watchlist/watched-at', [WatchlistController::class, 'updateWatchedAt'])->name('movies.watchlist.watched-at');
    Route::delete('/movies/{movie}/watchlist', [WatchlistController::class, 'destroy'])->name('movies.watchlist.destroy');
    Route::patch('/watchlist/privacy', [WatchlistController::class, 'updatePrivacy'])->name('watchlist.privacy');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/credits/import', [AdminCreditImportController::class, 'create'])->name('credits.import');

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
        Route::get('/people/search', [PersonController::class, 'search'])->name('people.search');
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

        Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
        Route::get('/genres/import', [GenreImportController::class, 'create'])->name('genres.import');
        Route::post('/genres/import', [GenreImportController::class, 'store'])->name('genres.import.store');
        Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
        Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
        Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
        Route::patch('/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');
        Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');
    });
});

require __DIR__.'/auth.php';

// Wildcards — must be last so they cannot shadow any specific route above.
Route::get('/movie/{movieSlug}', MovieBySlugController::class)->name('movies.public');
Route::get('/{typeSlug}/{personSlug}', PersonTypeCreditsController::class)->name('credits.by-type');
