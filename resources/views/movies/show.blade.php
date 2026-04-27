<x-app-layout>

    {{-- ═══════════════════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden bg-zinc-950 text-white">
        {{-- Blurred poster backdrop --}}
        @if($movie->posterUrl())
        <div class="absolute inset-0 pointer-events-none select-none" aria-hidden="true">
            <img src="{{ $movie->posterUrl() }}"
                 class="w-full h-full object-cover blur-2xl scale-110 opacity-40">
            <div class="absolute inset-0 bg-gradient-to-r from-zinc-950 via-zinc-950/60 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/70 via-transparent to-transparent"></div>
        </div>
        @endif

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="flex flex-col sm:flex-row gap-6 sm:gap-10 items-start">

                {{-- Poster --}}
                <div class="shrink-0 mx-auto sm:mx-0">
                    @if($movie->posterUrl())
                        <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                             class="w-40 sm:w-52 rounded-lg shadow-2xl ring-1 ring-zinc-700">
                    @else
                        <div class="w-40 sm:w-52 aspect-[2/3] bg-zinc-800 rounded-lg flex items-center justify-center ring-1 ring-zinc-700">
                            <span class="text-zinc-400 text-sm text-center px-4 leading-relaxed">{{ $movie->title }}</span>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight">{{ $movie->title }}</h1>

                    <p class="mt-1.5 text-zinc-400 text-sm">
                        {{ $movie->release_year }}
                        @if($movie->runtime)
                            @php
                                $hours   = intdiv($movie->runtime, 60);
                                $minutes = $movie->runtime % 60;
                            @endphp
                            &nbsp;·&nbsp;{{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes > 0 ? $minutes . 'm' : '' }}
                        @endif
                        @if(isset($crew['Director']))
                            &nbsp;·&nbsp;
                            {{ $crew['Director']->map(fn($c) => $c->person->name)->join(', ') }}
                        @endif
                    </p>

                    @if($genres->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($genres as $genre)
                                <a href="{{ route('movies.browse', ['genre' => $genre->slug]) }}"
                                   class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-700/80 text-zinc-300 hover:bg-amber-500 hover:text-zinc-950 transition">
                                    {{ $genre->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Rating + Stats --}}
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4">
                        @if($ratingCount === 0)
                            <span class="text-zinc-400 text-sm">No ratings yet</span>
                        @endif
                        @if($ratingCount > 0)
                            <div class="flex items-center gap-1.5">
                                <span class="text-yellow-400 text-xl font-bold">{{ number_format($avgRating, 1) }}</span>
                                <x-star-display :value="$avgRating" class="text-sm" />
                                <span class="text-zinc-400 text-xs">{{ $ratingCount }} {{ Str::plural('rating', $ratingCount) }}</span>
                            </div>
                            <span class="text-amber-300 hidden sm:inline">·</span>
                        @endif

                        <div class="flex items-center gap-4 text-zinc-400 text-sm">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ $watchedCount }} watched
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                                {{ $wantToWatchCount }} want to watch
                            </span>
                        </div>
                    </div>

                    {{-- User actions --}}
                    @auth
                    <div
                        x-data="starRating({{ $userRating?->stars ?? 0 }})"
                        class="mt-6 space-y-3"
                    >
                        <div class="flex items-center gap-4">
                            <x-movie-star-rating :movie="$movie" :userRating="$userRating" />
                        </div>
                        <x-movie-watchlist-actions
                            :movie="$movie"
                            :userWatchlistEntry="$userWatchlistEntry"
                            :userLists="$userLists"
                            :movieListIds="$movieListIds"
                        />
                    </div>
                    @else
                    <p class="mt-4 text-sm text-zinc-400">
                        <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300 transition">Sign in</a> to rate and log this film.
                    </p>
                    @endauth

                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         MOVIE INFO (synopsis, metadata, external links)
    ═══════════════════════════════════════════════════════════ --}}
    @if($movie->synopsis || $movie->country || $movie->language || $movie->imdb_url || $movie->letterboxd_url)
    <div class="border-t border-zinc-800 bg-zinc-900/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

            @if($movie->synopsis)
                <p class="text-zinc-300 text-sm leading-relaxed max-w-3xl">{{ $movie->synopsis }}</p>
            @endif

            @if($movie->country || $movie->language || $movie->imdb_url || $movie->letterboxd_url)
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-3 text-zinc-400 text-xs">

                    @if($movie->country)
                        <span>{{ $movie->country }}</span>
                    @endif

                    @if($movie->language)
                        <span>{{ $movie->language }}</span>
                    @endif

                    @if($movie->imdb_url)
                        <a href="{{ $movie->imdb_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-500/20 text-yellow-300 hover:bg-yellow-500/30 transition">
                            IMDb ↗
                        </a>
                    @endif

                    @if($movie->letterboxd_url)
                        <a href="{{ $movie->letterboxd_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 transition">
                            Letterboxd ↗
                        </a>
                    @endif

                </div>
            @endif

        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         CONTENT
    ═══════════════════════════════════════════════════════════ --}}
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-3 lg:gap-10 items-start">

                {{-- ── Sidebar: Cast / Crew ── --}}
                @if($cast->isNotEmpty() || $crew->isNotEmpty())
                @php
                    $castLimit      = 5;
                    $crewGroupLimit = 3;
                    $hiddenCastCount = max(0, $cast->count() - $castLimit);
                    $hiddenCrewCount = $crew->count() > $crewGroupLimit
                        ? $crew->skip($crewGroupLimit)->flatten()->count()
                        : 0;
                @endphp
                <div class="mb-8 lg:mb-0" x-data="{ tab: 'cast', castExpanded: false, crewExpanded: false }">
                    <div class="bg-zinc-900 rounded-lg overflow-hidden">
                        {{-- Tab bar --}}
                        <div class="border-b border-zinc-800 px-4">
                            <nav class="-mb-px flex gap-5">
                                <button type="button" @click="tab = 'cast'"
                                    :class="tab === 'cast' ? 'border-amber-500 text-amber-400' : 'border-transparent text-zinc-500 hover:text-zinc-300'"
                                    class="whitespace-nowrap border-b-2 py-3 text-sm font-medium transition-colors">
                                    Cast
                                </button>
                                <button type="button" @click="tab = 'crew'"
                                    :class="tab === 'crew' ? 'border-amber-500 text-amber-400' : 'border-transparent text-zinc-500 hover:text-zinc-300'"
                                    class="whitespace-nowrap border-b-2 py-3 text-sm font-medium transition-colors">
                                    Crew
                                </button>
                            </nav>
                        </div>

                        {{-- Cast --}}
                        <div x-show="tab === 'cast'" class="p-4">
                            @if($cast->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach($cast->take($castLimit) as $credit)
                                    <li class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 text-xs font-bold shrink-0 select-none">
                                            {{ strtoupper(substr($credit->person->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ $credit->byTypeUrl() }}"
                                               class="text-sm font-medium text-zinc-100 hover:text-amber-400 transition-colors block truncate">
                                                {{ $credit->person->name }}
                                            </a>
                                            @if($credit->character)
                                                <span class="text-xs text-zinc-500 block truncate">{{ $credit->character }}</span>
                                            @endif
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                                @if($hiddenCastCount > 0)
                                <ul class="space-y-3 mt-3" x-show="castExpanded" x-cloak>
                                    @foreach($cast->skip($castLimit) as $credit)
                                    <li class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 text-xs font-bold shrink-0 select-none">
                                            {{ strtoupper(substr($credit->person->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ $credit->byTypeUrl() }}"
                                               class="text-sm font-medium text-zinc-100 hover:text-amber-400 transition-colors block truncate">
                                                {{ $credit->person->name }}
                                            </a>
                                            @if($credit->character)
                                                <span class="text-xs text-zinc-500 block truncate">{{ $credit->character }}</span>
                                            @endif
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                                <button @click="castExpanded = !castExpanded"
                                        class="mt-3 text-xs text-amber-400 hover:text-amber-300 transition">
                                    <span x-show="!castExpanded">Show {{ $hiddenCastCount }} more</span>
                                    <span x-show="castExpanded" x-cloak>Show less</span>
                                </button>
                                @endif
                            @else
                                <p class="text-sm text-zinc-500">No cast listed.</p>
                            @endif
                        </div>

                        {{-- Crew --}}
                        <div x-show="tab === 'crew'" class="p-4">
                            @if($crew->isNotEmpty())
                                <dl class="space-y-4">
                                    @foreach($crew->take($crewGroupLimit) as $typeName => $credits)
                                    <div>
                                        <dt class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-1.5">{{ $typeName }}</dt>
                                        <dd class="space-y-1">
                                            @foreach($credits as $credit)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-500 text-xs font-bold shrink-0 select-none">
                                                    {{ strtoupper(substr($credit->person->name, 0, 1)) }}
                                                </div>
                                                <a href="{{ $credit->byTypeUrl() }}"
                                                   class="text-sm text-zinc-100 hover:text-amber-400 transition-colors truncate">
                                                    {{ $credit->person->name }}
                                                </a>
                                            </div>
                                            @endforeach
                                        </dd>
                                    </div>
                                    @endforeach
                                </dl>
                                @if($hiddenCrewCount > 0)
                                <dl class="space-y-4 mt-4" x-show="crewExpanded" x-cloak>
                                    @foreach($crew->skip($crewGroupLimit) as $typeName => $credits)
                                    <div>
                                        <dt class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-1.5">{{ $typeName }}</dt>
                                        <dd class="space-y-1">
                                            @foreach($credits as $credit)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-500 text-xs font-bold shrink-0 select-none">
                                                    {{ strtoupper(substr($credit->person->name, 0, 1)) }}
                                                </div>
                                                <a href="{{ $credit->byTypeUrl() }}"
                                                   class="text-sm text-zinc-100 hover:text-amber-400 transition-colors truncate">
                                                    {{ $credit->person->name }}
                                                </a>
                                            </div>
                                            @endforeach
                                        </dd>
                                    </div>
                                    @endforeach
                                </dl>
                                <button @click="crewExpanded = !crewExpanded"
                                        class="mt-3 text-xs text-amber-400 hover:text-amber-300 transition">
                                    <span x-show="!crewExpanded">Show {{ $hiddenCrewCount }} more</span>
                                    <span x-show="crewExpanded" x-cloak>Show less</span>
                                </button>
                                @endif
                            @else
                                <p class="text-sm text-zinc-500">No crew listed.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── Main: Friends' Activity + Reviews ── --}}
                <div class="{{ ($cast->isNotEmpty() || $crew->isNotEmpty()) ? 'lg:col-span-2' : 'lg:col-span-3' }}" id="reviews">

                {{-- Friends' Activity --}}
                @if($friendActivity->isNotEmpty())
                @php
                    // Build date→[friends] map; also include the viewer's own watched dates
                    // so that Amy + 1 friend sharing a date counts as a watch party.
                    $friendsByDate = [];

                    // Seed with the viewer's own watch dates (friends listed per date, not Amy herself)
                    $myWatchedDates = $userReviews
                        ->pluck('watched_at')
                        ->filter()
                        ->map->toDateString()
                        ->unique()
                        ->values();

                    foreach ($myWatchedDates as $dateStr) {
                        $friendsByDate[$dateStr] = [];   // placeholder — Amy watched, slot for friends
                    }

                    foreach ($friendActivity as $friend) {
                        foreach ($friend->watched_dates as $date) {
                            $key = $date->toDateString();
                            if (!array_key_exists($key, $friendsByDate)) {
                                $friendsByDate[$key] = [];
                            }
                            $existingIds = array_column($friendsByDate[$key], 'id');
                            if (!in_array($friend->user->id, $existingIds)) {
                                $friendsByDate[$key][] = $friend->user;
                            }
                        }
                    }

                    // A watch party needs at least 1 friend on a date Amy also watched,
                    // OR 2+ friends sharing a date regardless of Amy.
                    $watchPartyDates = array_keys(array_filter(
                        $friendsByDate,
                        fn($friends, $date) =>
                            count($friends) >= 2 ||                          // 2+ friends alone
                            (count($friends) >= 1 && in_array($date, $myWatchedDates->all())), // Amy + 1 friend
                        ARRAY_FILTER_USE_BOTH
                    ));
                @endphp
                <div class="bg-zinc-900 rounded-lg mb-5">
                    <div class="px-5 py-3 border-b border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-100">Friends</h2>
                    </div>
                    @if(count($watchPartyDates) > 0)
                        @foreach($watchPartyDates as $partyDate)
                        @php $partyFriends = $friendsByDate[$partyDate]; @endphp
                        <div class="px-5 py-2.5 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                            </svg>
                            <span class="text-xs text-amber-700 font-medium">Watch party · {{ \Carbon\Carbon::parse($partyDate)->format('j M Y') }}</span>
                            <span class="text-xs text-amber-600">
                                @if(in_array($partyDate, $myWatchedDates->all()))You and @endif{{ implode(', ', array_map(fn($u) => $u->name, $partyFriends)) }}
                            </span>
                        </div>
                        @endforeach
                    @endif
                    <ul class="divide-y divide-zinc-800">
                        @foreach($friendActivity as $friend)
                        <li class="flex items-center gap-3 px-5 py-3">
                            {{-- Avatar --}}
                            <a href="{{ route('profile.show', $friend->user->username) }}" class="shrink-0">
                                @if($friend->user->avatar)
                                    <img src="{{ asset('storage/' . $friend->user->avatar) }}"
                                         class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200" alt="">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 text-xs font-bold select-none">
                                        {{ strtoupper(substr($friend->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </a>

                            {{-- Name --}}
                            <a href="{{ route('profile.show', $friend->user->username) }}"
                               class="flex-1 min-w-0 text-sm font-medium text-zinc-200 hover:text-amber-400 transition-colors truncate">
                                {{ $friend->user->name }}
                            </a>

                            {{-- Activity --}}
                            <div class="shrink-0 flex items-center gap-2 text-sm text-zinc-500">
                                @if($friend->rating?->stars)
                                    <x-star-display :value="$friend->rating->stars" class="text-xs" emptyClass="text-zinc-700" />
                                @elseif($friend->rating?->liked)
                                    <svg class="w-4 h-4 text-red-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                @endif

                                @if($friend->review_count > 0)
                                    <span class="text-xs text-zinc-500">
                                        logged {{ $friend->review_count > 1 ? $friend->review_count . '×' : '' }}
                                        @if($friend->watched_dates->isNotEmpty())
                                            · {{ $friend->watched_dates->first()->format('j M Y') }}
                                        @endif
                                    </span>
                                @elseif($friend->watchlist?->list_type === 'watched' && !$friend->rating)
                                    <span class="text-xs text-zinc-500">watched</span>
                                @endif

                                @if($friend->watchlist?->list_type === 'want_to_watch')
                                    <span class="text-xs text-zinc-400">wants to watch</span>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                    <div class="bg-zinc-900 rounded-lg" x-data="{ open: false }">
                        <div class="px-5 py-4 border-b border-zinc-800 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-zinc-100">Reviews</h2>
                            @auth
                            <button type="button" @click="open = true" class="text-xs text-amber-400 hover:text-amber-300 transition font-medium">+ Review or Log</button>
                            @endauth
                        </div>

                        @if(session('status') === 'review-saved')
                            <p class="text-xs text-emerald-600 px-5 pt-3">Review saved.</p>
                        @endif
                        @if(session('status') === 'review-deleted')
                            <p class="text-xs text-zinc-500 px-5 pt-3">Review deleted.</p>
                        @endif
                        @if(session('status') === 'comment-posted')
                            <p class="text-xs text-emerald-600 px-5 pt-3">Comment posted.</p>
                        @endif
                        @if(session('status') === 'comment-deleted')
                            <p class="text-xs text-zinc-500 px-5 pt-3">Comment deleted.</p>
                        @endif

                        <div class="divide-y divide-gray-50">

                            {{-- Current user's reviews --}}
                            @auth
                            @if($userReviews->isNotEmpty())
                                <div class="px-5 py-2 bg-zinc-900 text-xs text-zinc-500">
                                    You've watched this {{ $userReviews->count() }} {{ Str::plural('time', $userReviews->count()) }}
                                </div>
                            @endif
                            @foreach($userReviews as $userReview)
                            <x-review-card :review="$userReview" :rating="$userRating" :is-own="true">
                                <form method="POST" action="{{ route('reviews.update', $userReview) }}">
                                    @csrf @method('PATCH')
                                    <div class="mb-2">
                                        <label class="block text-xs text-zinc-500 mb-1">Watch date</label>
                                        <input type="date" name="watched_at"
                                               value="{{ old('watched_at', $userReview->watched_at?->format('Y-m-d')) }}"
                                               class="input-dark text-sm">
                                    </div>
                                    <textarea name="body" rows="4"
                                        placeholder="Write your review… (optional)"
                                        class="input-dark w-full text-sm"
                                    >{{ old('body', $userReview->body) }}</textarea>
                                    @error('body')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                    <label class="flex items-center gap-2 mt-2 text-xs text-zinc-500 cursor-pointer select-none">
                                        <input type="checkbox" name="has_spoilers" value="1"
                                               {{ old('has_spoilers', $userReview->has_spoilers) ? 'checked' : '' }}
                                               class="rounded border-zinc-700 bg-zinc-800 text-amber-400">
                                        This review contains spoilers
                                    </label>
                                    <div class="flex items-center gap-3 mt-2">
                                        <button type="submit" class="btn-amber px-4 py-1.5 text-sm">Update</button>
                                        <button type="button" @click="editing = false" class="text-xs text-zinc-500 hover:text-zinc-400 transition underline">Cancel</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('reviews.destroy', $userReview) }}" class="mt-2">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition underline">Delete review</button>
                                </form>
                            </x-review-card>
                            @endforeach

                            {{-- Review or Log form --}}
                            <div id="review-form" class="px-5 py-4">
                                <button @click="open = true" x-show="!open"
                                        class="text-sm text-amber-400 hover:text-amber-300 transition font-medium">
                                    + Review or Log
                                </button>
                                <div x-show="open" x-cloak>
                                    <form method="POST" action="{{ route('movies.review.store', $movie) }}">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="block text-xs text-zinc-500 mb-1">Watch date</label>
                                            <input type="date" name="watched_at"
                                                   value="{{ old('watched_at', now()->format('Y-m-d')) }}"
                                                   class="input-dark text-sm">
                                        </div>
                                        <textarea name="body" rows="4"
                                            placeholder="Write your review… (optional)"
                                            class="input-dark w-full text-sm"
                                        >{{ old('body') }}</textarea>
                                        @error('body')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                        <label class="flex items-center gap-2 mt-2 text-xs text-zinc-500 cursor-pointer select-none">
                                            <input type="checkbox" name="has_spoilers" value="1"
                                                   {{ old('has_spoilers') ? 'checked' : '' }}
                                                   class="rounded border-zinc-700 bg-zinc-800 text-amber-400">
                                            This review contains spoilers
                                        </label>
                                        <div class="flex items-center gap-3 mt-2">
                                            <button type="submit" class="btn-amber px-4 py-1.5 text-sm">Save</button>
                                            <button type="button" @click="open = false" class="text-xs text-zinc-500 hover:text-zinc-400 transition underline">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endauth

                            {{-- Public reviews from other users --}}
                            @foreach($reviews as $review)
                            <x-review-card
                                :review="$review"
                                :rating="$reviewerRatings->get($review->user_id)"
                                :liked="$likedReviewIds->contains($review->id)" />
                            @endforeach

                            {{-- Empty state --}}
                            @if($reviews->isEmpty() && $userReviews->isEmpty())
                            <div class="px-5 py-6 text-sm text-zinc-500">
                                No reviews yet.
                                @guest
                                    <a href="{{ route('login') }}" class="text-amber-400 hover:underline">Sign in</a> to write one.
                                @endguest
                            </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         MORE FROM THIS DIRECTOR
    ═══════════════════════════════════════════════════════════ --}}
    @if($moreByDirector->isNotEmpty())
    <div class="border-t border-zinc-800 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @php
                $directorNames = isset($crew['Director'])
                    ? $crew['Director']->map(fn($c) => $c->person->name)->join(' & ')
                    : 'This Director';
            @endphp

            <h2 class="text-sm font-semibold text-zinc-100 mb-4">More from {{ $directorNames }}</h2>

            <div class="grid grid-cols-3 sm:grid-cols-6 gap-4">
                @foreach($moreByDirector as $film)
                <x-movie-poster-card :movie="$film" />
                @endforeach
            </div>

        </div>
    </div>
    @endif

</x-app-layout>
