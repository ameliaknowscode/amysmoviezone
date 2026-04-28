<x-app-layout :title="$profileUser->name">

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 shadow sm:rounded-lg overflow-hidden">

                {{-- Cover band --}}
                <div class="h-32 bg-zinc-800"></div>

                <div class="px-6 pb-6">

                    {{-- Avatar row: avatar left, action button right --}}
                    <div class="-mt-12 flex items-end justify-between mb-4">
                        <div>
                            @if($profileUser->avatar)
                                <img src="{{ asset('storage/' . $profileUser->avatar) }}"
                                     alt="{{ $profileUser->name }}"
                                     class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-md">
                            @else
                                <div class="inline-flex items-center justify-center h-24 w-24 rounded-full bg-amber-900/30 ring-4 ring-white shadow-md text-amber-400 text-3xl font-bold select-none">
                                    {{ strtoupper(substr($profileUser->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        @auth
                            @if(Auth::id() === $profileUser->id && $profileUser->username)
                                <a href="{{ route('profile.edit') }}"
                                   class="inline-flex items-center px-4 py-2 bg-amber-500 rounded-md text-xs text-white font-semibold uppercase tracking-widest hover:bg-amber-400 transition mb-1">
                                    Edit Profile
                                </a>
                            @elseif(Auth::id() !== $profileUser->id)
                                @if($isFollowing)
                                    <form method="POST" action="{{ route('follow.destroy', $profileUser->username) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-zinc-900 border border-zinc-700 rounded-md text-xs text-zinc-300 font-semibold uppercase tracking-widest hover:bg-zinc-800 transition mb-1">
                                            Following
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('follow.store', $profileUser->username) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-amber-500 rounded-md text-xs text-white font-semibold uppercase tracking-widest hover:bg-amber-400 transition mb-1">
                                            Follow
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endauth
                    </div>

                    {{-- Name + username --}}
                    <h1 class="text-2xl font-bold text-zinc-100 leading-tight">{{ $profileUser->name }}</h1>
                    <p class="text-sm text-zinc-400 mt-0.5">&#64;{{ $profileUser->username }}</p>

                    {{-- Social counts + member since --}}
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 text-sm">
                        <a href="{{ route('profile.followers', $profileUser->username) }}"
                           class="text-zinc-300 hover:text-amber-400 transition-colors">
                            <span class="font-semibold">{{ number_format($followerCount) }}</span>
                            <span class="text-zinc-400">{{ Str::plural('follower', $followerCount) }}</span>
                        </a>
                        <a href="{{ route('profile.following', $profileUser->username) }}"
                           class="text-zinc-300 hover:text-amber-400 transition-colors">
                            <span class="font-semibold">{{ number_format($followingCount) }}</span>
                            <span class="text-zinc-400">following</span>
                        </a>
                        <span class="text-zinc-400 hidden sm:inline">·</span>
                        <span class="text-zinc-400 text-xs">Member since {{ $profileUser->created_at->format('M Y') }}</span>
                    </div>

                    {{-- Activity stats --}}
                    @if(!$profileUser->profile_private)
                    <div class="flex flex-wrap gap-x-5 gap-y-2 mt-4">
                        <div class="text-center">
                            <div class="text-lg font-bold text-zinc-100">{{ number_format($totalRated) }}</div>
                            <div class="text-xs text-zinc-400 uppercase tracking-wide">Rated</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-zinc-100">{{ number_format($totalWatched) }}</div>
                            <div class="text-xs text-zinc-400 uppercase tracking-wide">Watched</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-zinc-100">{{ number_format($totalLogged) }}</div>
                            <div class="text-xs text-zinc-400 uppercase tracking-wide">Logged</div>
                        </div>
                    </div>
                    @endif

                    {{-- Sub-navigation: Diary / Watchlist --}}
                    @if(!$profileUser->profile_private)
                    <div class="mt-6 -mx-6 border-t border-zinc-800">
                        <nav class="flex overflow-x-auto">
                            <a href="{{ route('profile.diary', $profileUser->username) }}"
                               class="shrink-0 px-5 py-3 text-sm font-medium text-zinc-400 hover:text-amber-400 hover:bg-zinc-800 transition-colors border-b-2 border-transparent hover:border-amber-400">
                                Diary
                            </a>
                            <a href="{{ route('profile.watchlist', $profileUser->username) }}#want-to-watch"
                               class="shrink-0 px-5 py-3 text-sm font-medium text-zinc-400 hover:text-amber-400 hover:bg-zinc-800 transition-colors border-b-2 border-transparent hover:border-amber-400 flex items-center gap-1.5">
                                Want to Watch
                                @if($wantToWatchCount > 0)
                                <span class="bg-zinc-800 text-zinc-400 text-xs px-1.5 py-0.5 rounded-full font-normal">{{ $wantToWatchCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('profile.watchlist', $profileUser->username) }}#watched"
                               class="shrink-0 px-5 py-3 text-sm font-medium text-zinc-400 hover:text-amber-400 hover:bg-zinc-800 transition-colors border-b-2 border-transparent hover:border-amber-400 flex items-center gap-1.5">
                                Watched
                                @if($totalWatched > 0)
                                <span class="bg-zinc-800 text-zinc-400 text-xs px-1.5 py-0.5 rounded-full font-normal">{{ $totalWatched }}</span>
                                @endif
                            </a>
                            <a href="{{ route('profile.lists', $profileUser->username) }}"
                               class="shrink-0 px-5 py-3 text-sm font-medium text-zinc-400 hover:text-amber-400 hover:bg-zinc-800 transition-colors border-b-2 border-transparent hover:border-amber-400">
                                Lists
                            </a>
                        </nav>
                    </div>
                    @endif

                    {{-- Private profile message --}}
                    @if($profileUser->profile_private)
                    <div class="mt-6 pt-6 border-t border-zinc-800 text-sm text-zinc-400 italic">
                        This profile is private.
                    </div>
                    @endif

                    {{-- Recently rated --}}
                    @if($recentRatings->isNotEmpty())
                    <section aria-labelledby="profile-recently-rated-heading" class="mt-6 pt-6 border-t border-zinc-800">
                        <h2 id="profile-recently-rated-heading" class="text-sm font-semibold text-zinc-300 mb-3">Recently Rated</h2>
                        <div class="grid grid-cols-6 gap-2">
                            @foreach($recentRatings as $rating)
                            <a href="{{ $rating->movie->publicUrl() }}" class="group">
                                <div class="aspect-[2/3] bg-zinc-700 rounded overflow-hidden shadow-sm ring-1 ring-zinc-700">
                                    @if($rating->movie->posterUrl())
                                        <img src="{{ $rating->movie->posterUrl() }}" alt="{{ $rating->movie->title }}"
                                             class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center p-1">
                                            <span class="text-xs text-zinc-400 text-center leading-snug">{{ $rating->movie->title }}</span>
                                        </div>
                                    @endif
                                </div>
                                @if($rating->stars)
                                <div class="mt-0.5 flex justify-center">
                                    <x-star-display :value="$rating->stars" class="text-xs" />
                                </div>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    {{-- Recent reviews --}}
                    @if($recentReviews->isNotEmpty())
                    <section aria-labelledby="profile-recent-reviews-heading" class="mt-6 pt-6 border-t border-zinc-800">
                        <h2 id="profile-recent-reviews-heading" class="text-sm font-semibold text-zinc-300 mb-3">Recent Reviews</h2>
                        <div class="space-y-5">
                            @foreach($recentReviews as $review)
                            <div class="flex gap-3">
                                {{-- Small poster --}}
                                <a href="{{ $review->movie->publicUrl() }}" class="shrink-0 group">
                                    <div class="w-9 h-[54px] bg-zinc-700 rounded overflow-hidden shadow-sm">
                                        @if($review->movie->posterUrl())
                                            <img src="{{ $review->movie->posterUrl() }}" alt="{{ $review->movie->title }}"
                                                 class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                        @endif
                                    </div>
                                </a>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                        <a href="{{ $review->movie->publicUrl() }}"
                                           class="text-sm font-medium text-amber-400 hover:underline truncate">
                                            {{ $review->movie->title }}
                                        </a>
                                        @if($rating = $reviewRatings->get($review->movie_id))
                                            <x-star-display :value="$rating->stars" class="text-xs" />
                                        @endif
                                        @if($review->watched_at)
                                            <span class="text-xs text-zinc-400">{{ $review->watched_at->format('j M Y') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-zinc-300 mt-1 leading-relaxed line-clamp-3">{{ $review->body }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </section>
                    @endif

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
