<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Amy's Movie Zone
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($movieCount) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Movies</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($peopleCount) }}</div>
                    <div class="text-sm text-gray-500 mt-1">People</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($creditCount) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Credits</div>
                </div>
                <a href="{{ route('users.index') }}" class="bg-white rounded-lg shadow-sm p-6 text-center hover:bg-gray-50 transition-colors">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($memberCount) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Members</div>
                </a>
            </div>

            {{-- Recently Added --}}
            @if($recentMovies->isNotEmpty())
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recently Added</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($recentMovies as $movie)
                    <a href="{{ $movie->publicUrl() }}" class="group">
                        <div class="aspect-[2/3] bg-gray-200 rounded-md overflow-hidden shadow-sm">
                            @if($movie->posterUrl())
                                <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                                    class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                            @else
                                <div class="w-full h-full flex items-center justify-center p-2 text-center">
                                    <span class="text-xs text-gray-500 leading-snug">{{ $movie->title }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="mt-1.5">
                            <div class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ $movie->title }}</div>
                            @if($movie->release_year)
                            <div class="text-xs text-gray-500">{{ $movie->release_year }}</div>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recently Rated --}}
            <div x-data="{ tab: '{{ auth()->check() && $followingRatings->isNotEmpty() ? 'following' : 'all' }}' }">

                <div class="flex items-center gap-1 mb-4">
                    @auth
                        @if($followingRatings->isNotEmpty())
                        <button @click="tab = 'following'"
                                :class="tab === 'following' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'"
                                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors">
                            Following
                        </button>
                        @endif
                    @endauth
                    <button @click="tab = 'all'"
                            :class="tab === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors">
                        All Activity
                    </button>
                </div>

                {{-- Following feed --}}
                @auth
                    @if($followingRatings->isNotEmpty())
                    <div x-show="tab === 'following'">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach($followingRatings as $rating)
                            @include('partials.rating-card', ['rating' => $rating])
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endauth

                {{-- All activity feed --}}
                <div x-show="tab === 'all'">
                    @if($recentRatings->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($recentRatings as $rating)
                        @include('partials.rating-card', ['rating' => $rating])
                        @endforeach
                    </div>
                    @else
                        <p class="text-sm text-gray-400">No ratings yet.</p>
                    @endif
                </div>

            </div>

            {{-- Recent Reviews --}}
            @if($recentReviews->isNotEmpty())
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Reviews</h2>
                <div class="space-y-4">
                    @foreach($recentReviews as $review)
                    <div class="bg-white rounded-lg shadow-sm p-5 flex gap-4">
                        {{-- Poster --}}
                        <a href="{{ $review->movie->publicUrl() }}" class="flex-shrink-0 group">
                            <div class="w-12 h-[72px] bg-gray-200 rounded overflow-hidden shadow-sm">
                                @if($review->movie->posterUrl())
                                    <img src="{{ $review->movie->posterUrl() }}" alt="{{ $review->movie->title }}"
                                        class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                @endif
                            </div>
                        </a>
                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <a href="{{ route('profile.show', $review->user->username) }}"
                                   class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                                    {{ $review->user->name }}
                                </a>
                                <span class="text-xs text-gray-400">reviewed</span>
                                <a href="{{ $review->movie->publicUrl() }}"
                                   class="text-sm font-medium text-indigo-600 hover:underline truncate">
                                    {{ $review->movie->title }}
                                </a>
                                <span class="text-xs text-gray-400 ml-auto">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1 leading-relaxed line-clamp-2">{{ $review->body }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
