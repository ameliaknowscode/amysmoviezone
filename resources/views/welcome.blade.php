<x-app-layout title="Home">

    {{-- Cinematic hero --}}
    @if($featuredMovie)
    <section aria-label="Featured film" class="relative overflow-hidden min-h-[420px] flex items-center">
        {{-- Blurred poster backdrop --}}
        @if($featuredMovie->posterUrl())
        <div class="absolute inset-0 pointer-events-none select-none" aria-hidden="true">
            <img src="{{ $featuredMovie->posterUrl() }}"
                 class="w-full h-full object-cover blur-2xl scale-110 opacity-70">
            <div class="absolute inset-0 bg-gradient-to-r from-zinc-950 via-zinc-950/60 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/70 via-transparent to-transparent"></div>
        </div>
        @endif

        <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex items-center gap-10 sm:gap-16">

                {{-- Text content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-amber-400 text-xs font-semibold uppercase tracking-widest mb-4">Featured Film</p>
                    <h1 class="text-4xl sm:text-5xl font-black text-zinc-100 leading-tight">
                        {{ $featuredMovie->title }}
                    </h1>
                    @if($featuredMovie->release_year)
                    <p class="text-zinc-400 text-sm mt-2">{{ $featuredMovie->release_year }}</p>
                    @endif
                    @if($featuredMovie->genres->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($featuredMovie->genres->take(4) as $genre)
                        <span class="px-2.5 py-0.5 text-xs bg-zinc-800 text-zinc-300 rounded-full">{{ $genre->name }}</span>
                        @endforeach
                    </div>
                    @endif
                    @if($featuredMovie->synopsis)
                    <p class="text-zinc-400 text-sm leading-relaxed mt-4 line-clamp-3 max-w-lg">{{ $featuredMovie->synopsis }}</p>
                    @endif
                    <a href="{{ $featuredMovie->publicUrl() }}"
                       class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-zinc-950 text-sm font-bold rounded-lg transition">
                        View Film
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                {{-- Poster --}}
                @if($featuredMovie->posterUrl())
                <div class="hidden sm:block shrink-0">
                    <img src="{{ $featuredMovie->posterUrl() }}"
                         alt="{{ $featuredMovie->title }}"
                         class="w-40 sm:w-48 rounded-lg shadow-2xl ring-1 ring-zinc-700">
                </div>
                @endif

            </div>
        </div>
    </section>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

            {{-- Stats strip --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-zinc-900 rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-zinc-100">{{ number_format($movieCount) }}</div>
                    <div class="text-sm text-zinc-400 mt-1">Movies</div>
                </div>
                <div class="bg-zinc-900 rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-zinc-100">{{ number_format($peopleCount) }}</div>
                    <div class="text-sm text-zinc-400 mt-1">People</div>
                </div>
                <div class="bg-zinc-900 rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-zinc-100">{{ number_format($creditCount) }}</div>
                    <div class="text-sm text-zinc-400 mt-1">Credits</div>
                </div>
                <a href="{{ route('users.index') }}" class="bg-zinc-900 rounded-lg p-6 text-center hover:bg-zinc-800 transition-colors block">
                    <div class="text-3xl font-bold text-zinc-100">{{ number_format($memberCount) }}</div>
                    <div class="text-sm text-zinc-400 mt-1">Members</div>
                </a>
            </div>

            {{-- Recently Added --}}
            @if($recentMovies->isNotEmpty())
            <section aria-labelledby="recently-added-heading">
                <h2 id="recently-added-heading" class="text-lg font-semibold text-zinc-100 mb-4">Recently Added</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($recentMovies as $movie)
                    <x-movie-poster-card :movie="$movie" />
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Activity --}}
            <section aria-labelledby="activity-heading" x-data="{ tab: '{{ auth()->check() && $followingRatings->isNotEmpty() ? 'following' : 'all' }}' }">
                <h2 id="activity-heading" class="sr-only">Recent activity</h2>

                <div role="tablist" aria-label="Recent activity" class="flex items-center gap-2 mb-4"
                     @keydown.right.prevent="$event.target.nextElementSibling?.focus()"
                     @keydown.left.prevent="$event.target.previousElementSibling?.focus()">
                    @auth
                        @if($followingRatings->isNotEmpty())
                        <button @click="tab = 'following'"
                                @focus="tab = 'following'"
                                role="tab"
                                id="activity-tab-following"
                                aria-controls="activity-panel-following"
                                :aria-selected="tab === 'following' ? 'true' : 'false'"
                                :tabindex="tab === 'following' ? '0' : '-1'"
                                :class="tab === 'following' ? 'bg-amber-500 text-zinc-950' : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200'"
                                class="px-3 py-1.5 rounded-md text-sm font-semibold transition-colors">
                            Following
                        </button>
                        @endif
                    @endauth
                    <button @click="tab = 'all'"
                            @focus="tab = 'all'"
                            role="tab"
                            id="activity-tab-all"
                            aria-controls="activity-panel-all"
                            :aria-selected="tab === 'all' ? 'true' : 'false'"
                            :tabindex="tab === 'all' ? '0' : '-1'"
                            :class="tab === 'all' ? 'bg-amber-500 text-zinc-950' : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200'"
                            class="px-3 py-1.5 rounded-md text-sm font-semibold transition-colors">
                        All Activity
                    </button>
                </div>

                @auth
                    @if($followingRatings->isNotEmpty())
                    <div x-show="tab === 'following'"
                         role="tabpanel"
                         id="activity-panel-following"
                         aria-labelledby="activity-tab-following"
                         tabindex="0">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach($followingRatings as $rating)
                            @include('partials.rating-card', ['rating' => $rating])
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endauth

                <div x-show="tab === 'all'"
                     role="tabpanel"
                     id="activity-panel-all"
                     aria-labelledby="activity-tab-all"
                     tabindex="0">
                    @if($recentRatings->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($recentRatings as $rating)
                        @include('partials.rating-card', ['rating' => $rating])
                        @endforeach
                    </div>
                    @else
                        <p class="text-sm text-zinc-400">No ratings yet.</p>
                    @endif
                </div>

            </section>

            {{-- Recent Reviews --}}
            @if($recentReviews->isNotEmpty())
            <section aria-labelledby="recent-reviews-heading">
                <h2 id="recent-reviews-heading" class="text-lg font-semibold text-zinc-100 mb-4">Recent Reviews</h2>
                <div class="space-y-3">
                    @foreach($recentReviews as $review)
                    <div class="bg-zinc-900 rounded-lg p-5 flex gap-4">
                        <a href="{{ $review->movie->publicUrl() }}" class="flex-shrink-0 group">
                            <div class="w-12 h-[72px] bg-zinc-800 rounded overflow-hidden shadow-sm ring-1 ring-zinc-700">
                                @if($review->movie->posterUrl())
                                    <img src="{{ $review->movie->posterUrl() }}" alt="{{ $review->movie->title }}"
                                        class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                                @endif
                            </div>
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <a href="{{ route('profile.show', $review->user->username) }}"
                                   class="text-sm font-medium text-zinc-200 hover:text-amber-400 transition-colors">
                                    {{ $review->user->name }}
                                </a>
                                <span class="text-xs text-zinc-400">reviewed</span>
                                <a href="{{ $review->movie->publicUrl() }}"
                                   class="text-sm font-medium text-amber-400 hover:underline truncate">
                                    {{ $review->movie->title }}
                                </a>
                                <span class="text-xs text-zinc-400 ml-auto">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-zinc-400 mt-1 leading-relaxed line-clamp-2">{{ $review->body }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

        </div>
    </div>
</x-app-layout>
