<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Recommended for You</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if($tooFew)
                {{-- Not enough ratings yet --}}
                <div class="bg-zinc-900 sm:rounded-lg px-6 py-12 text-center">
                    <p class="text-3xl mb-3">🎬</p>
                    <h3 class="text-base font-semibold text-zinc-200 mb-1">Rate a few more films to get started</h3>
                    <p class="text-sm text-zinc-500">
                        You've rated {{ $rated }} {{ $rated === 1 ? 'film' : 'films' }}.
                        Rate at least {{ $needed }} to unlock personalised recommendations.
                    </p>
                    <a href="{{ route('movies.browse') }}"
                       class="mt-4 inline-block btn-amber px-4 py-2 text-sm-colors">
                        Browse Movies
                    </a>
                </div>

            @else

                {{-- ----------------------------------------------------------------
                     Taste Profile Panel
                ----------------------------------------------------------------- --}}
                @if(!empty($tasteProfile['genres']) || !empty($tasteProfile['directors']))
                    <div class="bg-zinc-900 sm:rounded-lg px-6 py-5">
                        <h3 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-4">Your taste profile</h3>
                        <div class="flex flex-wrap gap-8">

                            @if(!empty($tasteProfile['genres']))
                                <div>
                                    <p class="text-xs text-zinc-500 mb-2">Top genres</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($tasteProfile['genres'] as $genre)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-900/20 text-amber-300 ring-1 ring-amber-500/30">
                                                {{ $genre['name'] }}
                                                <span class="text-yellow-500 font-semibold">★ {{ $genre['avg_stars'] }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($tasteProfile['directors']))
                                <div>
                                    <p class="text-xs text-zinc-500 mb-2">Favourite directors</p>
                                    <div class="flex flex-wrap gap-x-5 gap-y-1">
                                        @foreach($tasteProfile['directors'] as $director)
                                            <span class="text-sm text-zinc-300">
                                                {{ $director['name'] }}
                                                <span class="text-xs text-yellow-500 font-semibold ml-1">★ {{ $director['avg_stars'] }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                @endif

                {{-- ----------------------------------------------------------------
                     No recommendations at all
                ----------------------------------------------------------------- --}}
                @if(! $hasAnyRecommendations)
                    <div class="bg-zinc-900 sm:rounded-lg px-6 py-12 text-center">
                        <p class="text-3xl mb-3">👀</p>
                        <h3 class="text-base font-semibold text-zinc-200 mb-1">No recommendations yet</h3>
                        <p class="text-sm text-zinc-500">
                            Keep rating films — we'll have suggestions for you soon!
                        </p>
                    </div>

                @else

                    {{-- ------------------------------------------------------------
                         Genre buckets
                    ------------------------------------------------------------- --}}
                    @foreach($genreBuckets as $bucket)
                        <section>
                            <h3 class="text-sm font-semibold text-zinc-300 mb-3 px-1">
                                Because you love <span class="text-amber-400">{{ $bucket['genre'] }}</span>
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-5">
                                @foreach($bucket['movies'] as $movie)
                                    <x-movie-poster-card :movie="$movie" />
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    {{-- ------------------------------------------------------------
                         Director buckets
                    ------------------------------------------------------------- --}}
                    @foreach($directorBuckets as $bucket)
                        <section>
                            <h3 class="text-sm font-semibold text-zinc-300 mb-3 px-1">
                                More from <span class="text-amber-400">{{ $bucket['director'] }}</span>
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-5">
                                @foreach($bucket['movies'] as $movie)
                                    <x-movie-poster-card :movie="$movie" />
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    {{-- ------------------------------------------------------------
                         Collaborative bucket
                    ------------------------------------------------------------- --}}
                    @if($collaborativeMovies->isNotEmpty())
                        <section>
                            <h3 class="text-sm font-semibold text-zinc-300 mb-1 px-1">Picked for you</h3>
                            <p class="text-xs text-zinc-500 mb-3 px-1">Based on members who rated the same films as you.</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-5">
                                @foreach($collaborativeMovies as $movie)
                                    <x-movie-poster-card
                                        :movie="$movie"
                                        :subline="$movie->recommender_count . ' ' . ($movie->recommender_count === 1 ? 'person' : 'people') . ' like you'" />
                                @endforeach
                            </div>
                        </section>
                    @endif

                @endif

            @endif

        </div>
    </div>
</x-app-layout>
