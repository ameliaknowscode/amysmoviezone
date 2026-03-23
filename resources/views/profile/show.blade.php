<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $profileUser->name }}
            </h2>

            @auth
                @if (Auth::id() === $profileUser->id && $profileUser->username)
                    <a href="{{ route('profile.edit') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Edit Profile
                    </a>
                @elseif (Auth::id() !== $profileUser->id)
                    @if ($isFollowing)
                        <form method="POST" action="{{ route('follow.destroy', $profileUser->username) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Following
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('follow.store', $profileUser->username) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Follow
                            </button>
                        </form>
                    @endif
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">

                <!-- Avatar / header band -->
                <div class="bg-indigo-600 h-24"></div>

                <div class="px-6 pb-6">
                    <!-- Avatar -->
                    <div class="-mt-10 mb-4">
                        @if($profileUser->avatar)
                            <img src="{{ asset('storage/' . $profileUser->avatar) }}"
                                 alt="{{ $profileUser->name }}"
                                 class="h-20 w-20 rounded-full object-cover ring-4 ring-white shadow">
                        @else
                            <div class="inline-flex items-center justify-center h-20 w-20 rounded-full bg-white ring-4 ring-white shadow text-indigo-600 text-2xl font-bold select-none">
                                {{ strtoupper(substr($profileUser->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900">{{ $profileUser->name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">&#64;{{ $profileUser->username }}</p>

                    {{-- Follower / following counts --}}
                    <div class="flex gap-4 mt-3">
                        <a href="{{ route('profile.followers', $profileUser->username) }}"
                           class="text-sm text-gray-700 hover:text-indigo-600 transition-colors">
                            <span class="font-semibold">{{ number_format($followerCount) }}</span>
                            <span class="text-gray-500">{{ Str::plural('follower', $followerCount) }}</span>
                        </a>
                        <a href="{{ route('profile.following', $profileUser->username) }}"
                           class="text-sm text-gray-700 hover:text-indigo-600 transition-colors">
                            <span class="font-semibold">{{ number_format($followingCount) }}</span>
                            <span class="text-gray-500">following</span>
                        </a>
                    </div>

                    <dl class="mt-6 space-y-3 text-sm text-gray-700">
                        <div class="flex gap-2">
                            <dt class="font-medium text-gray-500 w-32 shrink-0">Member since</dt>
                            <dd>{{ $profileUser->created_at->format('F j, Y') }}</dd>
                        </div>
                    </dl>

                    {{-- Watchlist links --}}
                    @if(!$profileUser->profile_private)
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Watchlists</h2>
                        <div class="flex gap-3">
                            <a href="{{ route('profile.watchlist', $profileUser->username) }}#want-to-watch"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                Want to Watch
                            </a>
                            <a href="{{ route('profile.watchlist', $profileUser->username) }}#watched"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                Watched
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Recently rated --}}
                    @if($recentRatings->isNotEmpty())
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Recently Rated</h2>
                        <div class="grid grid-cols-4 gap-3">
                            @foreach($recentRatings as $rating)
                            <a href="{{ $rating->movie->publicUrl() }}" class="group">
                                <div class="aspect-[2/3] bg-gray-200 rounded overflow-hidden shadow-sm">
                                    @if($rating->movie->posterUrl())
                                        <img src="{{ $rating->movie->posterUrl() }}" alt="{{ $rating->movie->title }}"
                                            class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center p-1 text-center">
                                            <span class="text-xs text-gray-500 leading-snug">{{ $rating->movie->title }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    <div class="text-xs font-medium text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ $rating->movie->title }}</div>
                                    @if($rating->stars)
                                    <div class="text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $rating->stars ? 'text-yellow-400' : 'text-gray-300' }}">&#9733;</span>
                                        @endfor
                                    </div>
                                    @endif
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Recent reviews --}}
                    @if($recentReviews->isNotEmpty())
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Recent Reviews</h2>
                        <div class="space-y-4">
                            @foreach($recentReviews as $review)
                            <div>
                                <a href="{{ $review->movie->publicUrl() }}" class="text-sm font-medium text-indigo-600 hover:underline">
                                    {{ $review->movie->title }}
                                </a>
                                <p class="text-sm text-gray-700 mt-1 leading-relaxed line-clamp-3">{{ $review->body }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $review->created_at->diffForHumans() }}</p>
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
