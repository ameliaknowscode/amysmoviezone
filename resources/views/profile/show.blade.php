<x-app-layout>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">

                {{-- Cover band --}}
                <div class="h-32 bg-gradient-to-br from-indigo-950 to-indigo-800"></div>

                <div class="px-6 pb-6">

                    {{-- Avatar row: avatar left, action button right --}}
                    <div class="-mt-12 flex items-end justify-between mb-4">
                        <div>
                            @if($profileUser->avatar)
                                <img src="{{ asset('storage/' . $profileUser->avatar) }}"
                                     alt="{{ $profileUser->name }}"
                                     class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-md">
                            @else
                                <div class="inline-flex items-center justify-center h-24 w-24 rounded-full bg-indigo-100 ring-4 ring-white shadow-md text-indigo-600 text-3xl font-bold select-none">
                                    {{ strtoupper(substr($profileUser->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        @auth
                            @if(Auth::id() === $profileUser->id && $profileUser->username)
                                <a href="{{ route('profile.edit') }}"
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md text-xs text-white font-semibold uppercase tracking-widest hover:bg-indigo-700 transition mb-1">
                                    Edit Profile
                                </a>
                            @elseif(Auth::id() !== $profileUser->id)
                                @if($isFollowing)
                                    <form method="POST" action="{{ route('follow.destroy', $profileUser->username) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs text-gray-700 font-semibold uppercase tracking-widest hover:bg-gray-50 transition mb-1">
                                            Following
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('follow.store', $profileUser->username) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md text-xs text-white font-semibold uppercase tracking-widest hover:bg-indigo-700 transition mb-1">
                                            Follow
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endauth
                    </div>

                    {{-- Name + username --}}
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $profileUser->name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">&#64;{{ $profileUser->username }}</p>

                    {{-- Social counts + member since --}}
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 text-sm">
                        <a href="{{ route('profile.followers', $profileUser->username) }}"
                           class="text-gray-700 hover:text-indigo-600 transition-colors">
                            <span class="font-semibold">{{ number_format($followerCount) }}</span>
                            <span class="text-gray-500">{{ Str::plural('follower', $followerCount) }}</span>
                        </a>
                        <a href="{{ route('profile.following', $profileUser->username) }}"
                           class="text-gray-700 hover:text-indigo-600 transition-colors">
                            <span class="font-semibold">{{ number_format($followingCount) }}</span>
                            <span class="text-gray-500">following</span>
                        </a>
                        <span class="text-gray-300 hidden sm:inline">·</span>
                        <span class="text-gray-400 text-xs">Member since {{ $profileUser->created_at->format('M Y') }}</span>
                    </div>

                    {{-- Activity stats --}}
                    @if(!$profileUser->profile_private)
                    <div class="flex flex-wrap gap-x-5 gap-y-2 mt-4">
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900">{{ number_format($totalRated) }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Rated</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900">{{ number_format($totalWatched) }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Watched</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900">{{ number_format($totalLogged) }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Logged</div>
                        </div>
                    </div>
                    @endif

                    {{-- Sub-navigation: Diary / Watchlist --}}
                    @if(!$profileUser->profile_private)
                    <div class="mt-6 -mx-6 border-t border-gray-100">
                        <nav class="flex overflow-x-auto">
                            <a href="{{ route('profile.diary', $profileUser->username) }}"
                               class="shrink-0 px-5 py-3 text-sm font-medium text-gray-500 hover:text-indigo-600 hover:bg-gray-50 transition-colors border-b-2 border-transparent hover:border-indigo-300">
                                Diary
                            </a>
                            <a href="{{ route('profile.watchlist', $profileUser->username) }}#want-to-watch"
                               class="shrink-0 px-5 py-3 text-sm font-medium text-gray-500 hover:text-indigo-600 hover:bg-gray-50 transition-colors border-b-2 border-transparent hover:border-indigo-300 flex items-center gap-1.5">
                                Want to Watch
                                @if($wantToWatchCount > 0)
                                <span class="bg-gray-100 text-gray-500 text-xs px-1.5 py-0.5 rounded-full font-normal">{{ $wantToWatchCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('profile.watchlist', $profileUser->username) }}#watched"
                               class="shrink-0 px-5 py-3 text-sm font-medium text-gray-500 hover:text-indigo-600 hover:bg-gray-50 transition-colors border-b-2 border-transparent hover:border-indigo-300 flex items-center gap-1.5">
                                Watched
                                @if($totalWatched > 0)
                                <span class="bg-gray-100 text-gray-500 text-xs px-1.5 py-0.5 rounded-full font-normal">{{ $totalWatched }}</span>
                                @endif
                            </a>
                            <a href="{{ route('profile.lists', $profileUser->username) }}"
                               class="shrink-0 px-5 py-3 text-sm font-medium text-gray-500 hover:text-indigo-600 hover:bg-gray-50 transition-colors border-b-2 border-transparent hover:border-indigo-300">
                                Lists
                            </a>
                        </nav>
                    </div>
                    @endif

                    {{-- Private profile message --}}
                    @if($profileUser->profile_private)
                    <div class="mt-6 pt-6 border-t border-gray-100 text-sm text-gray-400 italic">
                        This profile is private.
                    </div>
                    @endif

                    {{-- Recently rated --}}
                    @if($recentRatings->isNotEmpty())
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Recently Rated</h2>
                        <div class="grid grid-cols-6 gap-2">
                            @foreach($recentRatings as $rating)
                            <a href="{{ $rating->movie->publicUrl() }}" class="group">
                                <div class="aspect-[2/3] bg-gray-200 rounded overflow-hidden shadow-sm">
                                    @if($rating->movie->posterUrl())
                                        <img src="{{ $rating->movie->posterUrl() }}" alt="{{ $rating->movie->title }}"
                                             class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center p-1">
                                            <span class="text-xs text-gray-500 text-center leading-snug">{{ $rating->movie->title }}</span>
                                        </div>
                                    @endif
                                </div>
                                @if($rating->stars)
                                <div class="mt-0.5 flex justify-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-xs {{ $i <= $rating->stars ? 'text-yellow-400' : 'text-gray-300' }}">&#9733;</span>
                                    @endfor
                                </div>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Recent reviews --}}
                    @if($recentReviews->isNotEmpty())
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Recent Reviews</h2>
                        <div class="space-y-5">
                            @foreach($recentReviews as $review)
                            <div class="flex gap-3">
                                {{-- Small poster --}}
                                <a href="{{ $review->movie->publicUrl() }}" class="shrink-0 group">
                                    <div class="w-9 h-[54px] bg-gray-200 rounded overflow-hidden shadow-sm">
                                        @if($review->movie->posterUrl())
                                            <img src="{{ $review->movie->posterUrl() }}" alt="{{ $review->movie->title }}"
                                                 class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                        @endif
                                    </div>
                                </a>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                        <a href="{{ $review->movie->publicUrl() }}"
                                           class="text-sm font-medium text-indigo-600 hover:underline truncate">
                                            {{ $review->movie->title }}
                                        </a>
                                        @if($rating = $reviewRatings->get($review->movie_id))
                                            <span class="text-xs leading-none">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="{{ $i <= $rating->stars ? 'text-yellow-400' : 'text-gray-300' }}">&#9733;</span>
                                                @endfor
                                            </span>
                                        @endif
                                        @if($review->watched_at)
                                            <span class="text-xs text-gray-400">{{ $review->watched_at->format('j M Y') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-700 mt-1 leading-relaxed line-clamp-3">{{ $review->body }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
