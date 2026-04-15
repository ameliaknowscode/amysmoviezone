<x-app-layout>

    {{-- ═══════════════════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════════════════ --}}
    <div class="bg-indigo-950 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="flex flex-col sm:flex-row gap-6 sm:gap-10 items-start">

                {{-- Poster --}}
                <div class="shrink-0 mx-auto sm:mx-0">
                    @if($movie->posterUrl())
                        <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                             class="w-40 sm:w-52 rounded-lg shadow-2xl ring-1 ring-white/10">
                    @else
                        <div class="w-40 sm:w-52 aspect-[2/3] bg-indigo-900 rounded-lg flex items-center justify-center ring-1 ring-white/10">
                            <span class="text-indigo-300 text-sm text-center px-4 leading-relaxed">{{ $movie->title }}</span>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight">{{ $movie->title }}</h1>

                    <p class="mt-1.5 text-indigo-300 text-sm">
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
                                   class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-800 text-indigo-200 hover:bg-indigo-700 transition">
                                    {{ $genre->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Rating + Stats --}}
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4">
                        @if($ratingCount === 0)
                            <span class="text-indigo-300 text-sm">No ratings yet</span>
                        @endif
                        @if($ratingCount > 0)
                            <div class="flex items-center gap-1.5">
                                <span class="text-yellow-400 text-xl font-bold">{{ number_format($avgRating, 1) }}</span>
                                <x-star-display :value="$avgRating" class="text-sm" emptyClass="text-indigo-800" />
                                <span class="text-indigo-300 text-xs">{{ $ratingCount }} {{ Str::plural('rating', $ratingCount) }}</span>
                            </div>
                            <span class="text-indigo-800 hidden sm:inline">·</span>
                        @endif

                        <div class="flex items-center gap-4 text-indigo-300 text-sm">
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
                        x-data="{
                            stars: {{ $userRating?->stars ?? 0 }},
                            hovered: 0,
                        }"
                        class="mt-6 space-y-3"
                    >
                        {{-- Stars + Remove + Heart --}}
                        <div class="flex items-center gap-4">
                            <form method="POST" action="{{ route('movies.rate', $movie) }}" x-ref="ratingForm" class="flex items-center">
                                @csrf
                                <input type="hidden" name="stars" x-bind:value="stars">
                                @foreach([1,2,3,4,5] as $star)
                                {{-- Single star span: mousemove detects left (half) vs right (full) half --}}
                                <span
                                    class="relative inline-block text-2xl sm:text-3xl leading-none cursor-pointer select-none"
                                    @mousemove="hovered = $event.offsetX < $el.offsetWidth / 2 ? {{ $star - 0.5 }} : {{ $star }}"
                                    @mouseleave="hovered = 0"
                                    @click="stars = hovered; $nextTick(() => $refs.ratingForm.submit())"
                                    :title="(hovered || stars) + ' ' + ((hovered || stars) === 1 ? 'star' : 'stars')"
                                >
                                    {{-- Base star (full or empty) --}}
                                    <span class="transition-colors"
                                          :class="(hovered || stars) >= {{ $star }} ? 'text-yellow-400' : 'text-indigo-700'"
                                    >&#9733;</span>
                                    {{-- Half overlay --}}
                                    <span
                                        class="absolute top-0 left-0 text-yellow-400 pointer-events-none transition-opacity"
                                        :class="(hovered || stars) >= {{ $star - 0.5 }} && (hovered || stars) < {{ $star }} ? 'opacity-100' : 'opacity-0'"
                                        style="clip-path: inset(0 50% 0 0)"
                                    >&#9733;</span>
                                </span>
                                @endforeach
                            </form>

                            @if($userRating?->stars)
                            <form method="POST" action="{{ route('movies.rating.destroy', $movie) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-indigo-400 hover:text-indigo-200 transition underline">
                                    Remove
                                </button>
                            </form>
                            @endif

                            {{-- Heart --}}
                            <form method="POST" action="{{ route('movies.rate', $movie) }}">
                                @csrf
                                <input type="hidden" name="liked" value="{{ $userRating?->liked ? '0' : '1' }}">
                                <button
                                    type="submit"
                                    class="focus:outline-none transition-colors {{ $userRating?->liked ? 'text-red-400' : 'text-indigo-700 hover:text-red-400' }}"
                                    title="{{ $userRating?->liked ? 'Unlike' : 'Like' }}"
                                >
                                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        {{-- Watchlist buttons --}}
                        @php $listType = $userWatchlistEntry?->list_type; @endphp
                        <div class="flex flex-wrap items-center gap-2">
                            @if($listType === 'want_to_watch')
                                <form method="POST" action="{{ route('movies.watchlist.destroy', $movie) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-500 transition font-medium">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Want to Watch
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('movies.watchlist.store', $movie) }}">
                                    @csrf
                                    <input type="hidden" name="list_type" value="want_to_watch">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-indigo-700 text-indigo-200 hover:border-indigo-400 hover:text-white transition font-medium">
                                        + Want to Watch
                                    </button>
                                </form>
                            @endif

                            @if($listType === 'watched')
                                <div x-data="{ editing: false }">
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('movies.watchlist.destroy', $movie) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md bg-emerald-600 text-white hover:bg-emerald-500 transition font-medium">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Watched
                                            </button>
                                        </form>
                                        <button @click="editing = !editing"
                                                class="text-xs text-indigo-300 hover:text-white transition">
                                            @if($userWatchlistEntry->watched_at)
                                                {{ $userWatchlistEntry->watched_at->format('j M Y') }}
                                            @else
                                                + add date
                                            @endif
                                        </button>
                                    </div>
                                    <form x-show="editing" x-cloak method="POST"
                                          action="{{ route('movies.watchlist.watched-at', $movie) }}"
                                          class="flex items-center gap-2 mt-2">
                                        @csrf @method('PATCH')
                                        <input type="date" name="watched_at"
                                               value="{{ $userWatchlistEntry->watched_at?->format('Y-m-d') }}"
                                               max="{{ now()->format('Y-m-d') }}"
                                               class="text-xs rounded border border-indigo-700 bg-indigo-950 text-indigo-100 px-2 py-1 focus:outline-none focus:border-indigo-400">
                                        <button type="submit" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition">Save</button>
                                        <button type="button" @click="editing = false" class="text-xs text-indigo-400 hover:text-indigo-200 transition">Cancel</button>
                                    </form>
                                </div>
                            @else
                                <div x-data="{ open: false }">
                                    <button @click="open = true"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-indigo-700 text-indigo-200 hover:border-indigo-400 hover:text-white transition font-medium">
                                        + Watched
                                    </button>
                                    <form x-show="open" x-cloak method="POST"
                                          action="{{ route('movies.watchlist.store', $movie) }}"
                                          class="flex items-center gap-2 mt-2">
                                        @csrf
                                        <input type="hidden" name="list_type" value="watched">
                                        <input type="date" name="watched_at"
                                               value="{{ now()->format('Y-m-d') }}"
                                               max="{{ now()->format('Y-m-d') }}"
                                               class="text-xs rounded border border-indigo-700 bg-indigo-950 text-indigo-100 px-2 py-1 focus:outline-none focus:border-indigo-400">
                                        <button type="submit" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition">Mark Watched</button>
                                        <button type="button" @click="open = false" class="text-xs text-indigo-400 hover:text-indigo-200 transition">Cancel</button>
                                    </form>
                                </div>
                            @endif

                            <a href="#reviews" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-indigo-300 hover:text-white transition">
                                + Review or Log
                            </a>

                            {{-- Add to List --}}
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button @click="open = !open"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-indigo-400 text-indigo-200 hover:bg-indigo-800 transition font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h8"/>
                                    </svg>
                                    Lists
                                </button>
                                <div x-show="open" x-cloak
                                     class="absolute left-0 mt-1.5 w-56 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-20">
                                    @forelse($userLists as $list)
                                        @php $inList = $movieListIds->contains($list->id); @endphp
                                        <form method="POST"
                                              action="{{ $inList
                                                  ? route('lists.movies.destroy', [$list, $movie])
                                                  : route('lists.movies.store', $list) }}">
                                            @csrf
                                            @if($inList) @method('DELETE') @else
                                                <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                            @endif
                                            <button type="submit"
                                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                                <span class="w-4 h-4 shrink-0 flex items-center justify-center">
                                                    @if($inList)
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                                <span class="truncate">{{ $list->name }}</span>
                                            </button>
                                        </form>
                                    @empty
                                        <p class="px-4 py-2 text-xs text-gray-400">No lists yet.</p>
                                    @endforelse
                                    <div class="border-t border-gray-100 mt-1 pt-1">
                                        <a href="{{ route('lists.create') }}"
                                           class="flex items-center gap-2.5 px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            New list
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="mt-4 text-sm text-indigo-300">
                        <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 transition">Sign in</a> to rate and log this film.
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
    <div class="border-t border-indigo-900 bg-indigo-950/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

            @if($movie->synopsis)
                <p class="text-indigo-100 text-sm leading-relaxed max-w-3xl">{{ $movie->synopsis }}</p>
            @endif

            @if($movie->country || $movie->language || $movie->imdb_url || $movie->letterboxd_url)
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-3 text-indigo-300 text-xs">

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
                <div class="mb-8 lg:mb-0" x-data="{ tab: 'cast' }">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        {{-- Tab bar --}}
                        <div class="border-b border-gray-100 px-4">
                            <nav class="-mb-px flex gap-5">
                                <button type="button" @click="tab = 'cast'"
                                    :class="tab === 'cast' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                    class="whitespace-nowrap border-b-2 py-3 text-sm font-medium transition-colors">
                                    Cast
                                </button>
                                <button type="button" @click="tab = 'crew'"
                                    :class="tab === 'crew' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                    class="whitespace-nowrap border-b-2 py-3 text-sm font-medium transition-colors">
                                    Crew
                                </button>
                            </nav>
                        </div>

                        {{-- Cast --}}
                        <div x-show="tab === 'cast'" class="p-4">
                            @if($cast->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach($cast as $credit)
                                    <li class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shrink-0 select-none">
                                            {{ strtoupper(substr($credit->person->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ $credit->byTypeUrl() }}"
                                               class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors block truncate">
                                                {{ $credit->person->name }}
                                            </a>
                                            @if($credit->character)
                                                <span class="text-xs text-gray-400 block truncate">{{ $credit->character }}</span>
                                            @endif
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-400">No cast listed.</p>
                            @endif
                        </div>

                        {{-- Crew --}}
                        <div x-show="tab === 'crew'" class="p-4">
                            @if($crew->isNotEmpty())
                                <dl class="space-y-4">
                                    @foreach($crew as $typeName => $credits)
                                    <div>
                                        <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">{{ $typeName }}</dt>
                                        <dd class="space-y-1">
                                            @foreach($credits as $credit)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-bold shrink-0 select-none">
                                                    {{ strtoupper(substr($credit->person->name, 0, 1)) }}
                                                </div>
                                                <a href="{{ $credit->byTypeUrl() }}"
                                                   class="text-sm text-gray-900 hover:text-indigo-600 transition-colors truncate">
                                                    {{ $credit->person->name }}
                                                </a>
                                            </div>
                                            @endforeach
                                        </dd>
                                    </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="text-sm text-gray-400">No crew listed.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── Main: Friends' Activity + Reviews ── --}}
                <div class="{{ ($cast->isNotEmpty() || $crew->isNotEmpty()) ? 'lg:col-span-2' : 'lg:col-span-3' }}" id="reviews">

                {{-- Friends' Activity --}}
                @if($friendActivity->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm mb-5">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Friends</h2>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach($friendActivity as $friend)
                        <li class="flex items-center gap-3 px-5 py-3">
                            {{-- Avatar --}}
                            <a href="{{ route('profile.show', $friend->user->username) }}" class="shrink-0">
                                @if($friend->user->avatar)
                                    <img src="{{ asset('storage/' . $friend->user->avatar) }}"
                                         class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200" alt="">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold select-none">
                                        {{ strtoupper(substr($friend->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </a>

                            {{-- Name --}}
                            <a href="{{ route('profile.show', $friend->user->username) }}"
                               class="flex-1 min-w-0 text-sm font-medium text-gray-800 hover:text-indigo-600 transition-colors truncate">
                                {{ $friend->user->name }}
                            </a>

                            {{-- Activity --}}
                            <div class="shrink-0 flex items-center gap-2 text-sm text-gray-500">
                                @if($friend->rating?->stars)
                                    <x-star-display :value="$friend->rating->stars" class="text-xs" emptyClass="text-gray-200" />
                                @elseif($friend->rating?->liked)
                                    <svg class="w-4 h-4 text-red-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                @endif

                                @if($friend->review_count > 0)
                                    <span class="text-xs text-gray-400">
                                        logged {{ $friend->review_count > 1 ? $friend->review_count . '×' : '' }}
                                    </span>
                                @elseif($friend->watchlist?->list_type === 'watched' && !$friend->rating)
                                    <span class="text-xs text-gray-400">watched</span>
                                @endif

                                @if($friend->watchlist?->list_type === 'want_to_watch')
                                    <span class="text-xs text-indigo-400">wants to watch</span>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-900">Reviews</h2>
                            @auth
                            <a href="#review-form" class="text-xs text-indigo-600 hover:text-indigo-800 transition font-medium">+ Review or Log</a>
                            @endauth
                        </div>

                        @if(session('status') === 'review-saved')
                            <p class="text-xs text-emerald-600 px-5 pt-3">Review saved.</p>
                        @endif
                        @if(session('status') === 'review-deleted')
                            <p class="text-xs text-gray-400 px-5 pt-3">Review deleted.</p>
                        @endif

                        <div class="divide-y divide-gray-50">

                            {{-- Current user's reviews --}}
                            @auth
                            @if($userReviews->isNotEmpty())
                                <div class="px-5 py-2 bg-gray-50 text-xs text-gray-400">
                                    You've watched this {{ $userReviews->count() }} {{ Str::plural('time', $userReviews->count()) }}
                                </div>
                            @endif
                            @foreach($userReviews as $userReview)
                            <div x-data="{ editing: false }" class="px-5 py-4">
                                <div class="flex gap-3">
                                    <div class="shrink-0">
                                        @if(auth()->user()->avatar)
                                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                                 alt="{{ auth()->user()->name }}"
                                                 class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200">
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm select-none">
                                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">

                                        {{-- Read view --}}
                                        <div x-show="!editing">
                                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                                <span class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</span>
                                                @if($userRating?->stars)
                                                    <x-star-display :value="$userRating->stars" class="text-xs" />
                                                @endif
                                                @if($userReview->watched_at)
                                                    <span class="text-xs text-gray-400">watched {{ $userReview->watched_at->format('j M Y') }}</span>
                                                @endif
                                                @if($userReview->is_rewatch)
                                                    <span class="text-xs text-indigo-500 border border-indigo-200 rounded px-1.5 py-0.5 leading-none">Rewatch</span>
                                                @endif
                                                @if($userReview->has_spoilers)
                                                    <span class="text-xs text-amber-600 border border-amber-200 rounded px-1.5 py-0.5 leading-none">Spoilers</span>
                                                @endif
                                                <button @click="editing = true"
                                                        class="text-xs text-indigo-500 hover:text-indigo-700 transition underline">
                                                    Edit
                                                </button>
                                            </div>
                                            @if($userReview->body)
                                                <p class="text-sm text-gray-700 mt-1.5 leading-relaxed">{{ $userReview->body }}</p>
                                            @endif
                                        </div>

                                        {{-- Edit form --}}
                                        <div x-show="editing" x-cloak>
                                            <form method="POST" action="{{ route('reviews.update', $userReview) }}">
                                                @csrf @method('PATCH')
                                                <div class="mb-2">
                                                    <label class="block text-xs text-gray-500 mb-1">Watch date</label>
                                                    <input type="date" name="watched_at"
                                                           value="{{ old('watched_at', $userReview->watched_at?->format('Y-m-d')) }}"
                                                           class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                </div>
                                                <textarea name="body" rows="4"
                                                    placeholder="Write your review… (optional)"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >{{ old('body', $userReview->body) }}</textarea>
                                                @error('body')
                                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                                @enderror
                                                <label class="flex items-center gap-2 mt-2 text-xs text-gray-500 cursor-pointer select-none">
                                                    <input type="checkbox" name="has_spoilers" value="1"
                                                           {{ old('has_spoilers', $userReview->has_spoilers) ? 'checked' : '' }}
                                                           class="rounded border-gray-300 text-indigo-600">
                                                    This review contains spoilers
                                                </label>
                                                <div class="flex items-center gap-3 mt-2">
                                                    <button type="submit" class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Update</button>
                                                    <button type="button" @click="editing = false" class="text-xs text-gray-400 hover:text-gray-600 transition underline">Cancel</button>
                                                </div>
                                            </form>
                                            <form method="POST" action="{{ route('reviews.destroy', $userReview) }}" class="mt-2">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition underline">Delete review</button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            @endforeach

                            {{-- Review or Log form --}}
                            <div id="review-form" x-data="{ open: false }" class="px-5 py-4">
                                <button @click="open = true" x-show="!open"
                                        class="text-sm text-indigo-600 hover:text-indigo-800 transition font-medium">
                                    + Review or Log
                                </button>
                                <div x-show="open" x-cloak>
                                    <form method="POST" action="{{ route('movies.review.store', $movie) }}">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="block text-xs text-gray-500 mb-1">Watch date</label>
                                            <input type="date" name="watched_at"
                                                   value="{{ old('watched_at', now()->format('Y-m-d')) }}"
                                                   class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <textarea name="body" rows="4"
                                            placeholder="Write your review… (optional)"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >{{ old('body') }}</textarea>
                                        @error('body')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                        <label class="flex items-center gap-2 mt-2 text-xs text-gray-500 cursor-pointer select-none">
                                            <input type="checkbox" name="has_spoilers" value="1"
                                                   {{ old('has_spoilers') ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-indigo-600">
                                            This review contains spoilers
                                        </label>
                                        <div class="flex items-center gap-3 mt-2">
                                            <button type="submit" class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Save</button>
                                            <button type="button" @click="open = false" class="text-xs text-gray-400 hover:text-gray-600 transition underline">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endauth

                            {{-- Public reviews from other users --}}
                            @foreach($reviews as $review)
                            <div class="px-5 py-4 flex gap-3">
                                <div class="shrink-0">
                                    @if($review->user->avatar)
                                        <img src="{{ asset('storage/' . $review->user->avatar) }}"
                                             alt="{{ $review->user->name }}"
                                             class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm select-none">
                                            {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                        <a href="{{ route('profile.show', $review->user->username) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                                            {{ $review->user->name }}
                                        </a>
                                        @if($rating = $reviewerRatings->get($review->user_id))
                                            <x-star-display :value="$rating->stars" class="text-xs" />
                                        @endif
                                        @if($review->watched_at)
                                            <span class="text-xs text-gray-400">watched {{ $review->watched_at->format('j M Y') }}</span>
                                        @endif
                                        @if($review->has_spoilers)
                                            <span class="text-xs text-amber-600 border border-amber-200 rounded px-1.5 py-0.5 leading-none">Spoilers</span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($review->body)
                                        @if($review->has_spoilers)
                                            <div x-data="{ revealed: false }" class="mt-1.5">
                                                <div x-show="!revealed"
                                                     class="text-sm text-gray-400 italic cursor-pointer hover:text-gray-600 transition-colors"
                                                     @click="revealed = true">
                                                    ⚠ Spoilers hidden — click to reveal
                                                </div>
                                                <p x-show="revealed" x-cloak
                                                   class="text-sm text-gray-700 leading-relaxed">{{ $review->body }}</p>
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-700 mt-1.5 leading-relaxed">{{ $review->body }}</p>
                                        @endif
                                    @endif

                                    {{-- Like button --}}
                                    @auth
                                    @php $liked = $likedReviewIds->contains($review->id); @endphp
                                    <div class="mt-2">
                                        <form method="POST"
                                              action="{{ $liked
                                                  ? route('reviews.likes.destroy', $review)
                                                  : route('reviews.likes.store', $review) }}">
                                            @csrf
                                            @if($liked) @method('DELETE') @endif
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-xs transition-colors {{ $liked ? 'text-red-400 hover:text-red-500' : 'text-gray-300 hover:text-red-400' }}">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                                </svg>
                                                @if($review->likes_count > 0)
                                                    {{ $review->likes_count }}
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                    @endauth
                                </div>
                            </div>
                            @endforeach

                            {{-- Empty state --}}
                            @if($reviews->isEmpty() && $userReviews->isEmpty())
                            <div class="px-5 py-6 text-sm text-gray-400">
                                No reviews yet.
                                @guest
                                    <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Sign in</a> to write one.
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
    <div class="border-t border-gray-100 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @php
                $directorNames = isset($crew['Director'])
                    ? $crew['Director']->map(fn($c) => $c->person->name)->join(' & ')
                    : 'This Director';
            @endphp

            <h2 class="text-sm font-semibold text-gray-900 mb-4">More from {{ $directorNames }}</h2>

            <div class="grid grid-cols-3 sm:grid-cols-6 gap-4">
                @foreach($moreByDirector as $film)
                <a href="{{ $film->publicUrl() }}" class="group">
                    <div class="aspect-[2/3] bg-gray-200 rounded-md overflow-hidden shadow-sm">
                        @if($film->posterUrl())
                            <img src="{{ $film->posterUrl() }}" alt="{{ $film->title }}"
                                 class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                        @else
                            <div class="w-full h-full flex items-center justify-center p-2 text-center">
                                <span class="text-xs text-gray-500 leading-snug">{{ $film->title }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-1.5">
                        <div class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-600 transition-colors">
                            {{ $film->title }}
                        </div>
                        @if($film->release_year)
                            <div class="text-xs text-gray-500">{{ $film->release_year }}</div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>

        </div>
    </div>
    @endif

</x-app-layout>
