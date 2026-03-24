<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $movie->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex gap-8 mb-6">

                        @if($movie->posterUrl())
                        <div class="flex-shrink-0">
                            <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }} poster"
                                class="w-[230px] h-[345px] object-cover rounded-md shadow-sm">
                        </div>
                        @endif

                        <div class="flex-1 min-w-0">

                            <dl class="divide-y divide-gray-100">
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $movie->title }}</dd>
                                </div>
                                @if(isset($crew['Director']))
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">{{ $crew['Director']->count() === 1 ? 'Director' : 'Directors' }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        @foreach($crew['Director'] as $i => $c)
                                            @if($i > 0), @endif
                                            <a href="{{ $c->byTypeUrl() }}" class="text-indigo-600 hover:underline">{{ $c->person->name }}</a>
                                        @endforeach
                                    </dd>
                                </div>
                                @endif
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Release Year</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $movie->release_year }}</dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Average Rating</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                        @if($ratingCount > 0)
                                            <span class="text-yellow-400">★</span> {{ number_format($avgRating, 1) }} <span class="text-gray-500">({{ $ratingCount }} {{ Str::plural('rating', $ratingCount) }})</span>
                                        @else
                                            <span class="text-gray-400">No ratings yet</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Want to Watch</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $wantToWatchCount }}</dd>
                                </div>
                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Watched</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $watchedCount }}</dd>
                                </div>
                            </dl>

                            @auth
                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-700 mb-4">Your Rating &amp; Watchlist</h3>

                                {{-- Star rating + heart --}}
                                <div
                                    x-data="{
                                        stars: {{ $userRating?->stars ?? 0 }},
                                        hovered: 0,
                                    }"
                                    class="flex items-center gap-4 mb-4"
                                >
                                    {{-- Stars --}}
                                    <form method="POST" action="{{ route('movies.rate', $movie) }}" class="flex items-center gap-1" id="star-form-{{ $movie->id }}">
                                        @csrf
                                        <input type="hidden" name="stars" x-bind:value="stars">
                                        @foreach([1,2,3,4,5] as $star)
                                        <button
                                            type="submit"
                                            @mouseenter="hovered = {{ $star }}"
                                            @mouseleave="hovered = 0"
                                            @click="stars = {{ $star }}"
                                            class="text-2xl leading-none focus:outline-none transition-colors"
                                            :class="(hovered ? hovered >= {{ $star }} : stars >= {{ $star }}) ? 'text-yellow-400' : 'text-gray-300'"
                                            title="{{ $star }} star{{ $star !== 1 ? 's' : '' }}"
                                            aria-label="{{ $star }} star{{ $star !== 1 ? 's' : '' }}"
                                        >&#9733;</button>
                                        @endforeach
                                    </form>

                                    {{-- Remove rating --}}
                                    @if($userRating?->stars)
                                    <form method="POST" action="{{ route('movies.rating.destroy', $movie) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 underline">Remove rating</button>
                                    </form>
                                    @endif

                                    {{-- Heart / liked toggle --}}
                                    <form method="POST" action="{{ route('movies.rate', $movie) }}">
                                        @csrf
                                        <input type="hidden" name="liked" value="{{ $userRating?->liked ? '0' : '1' }}">
                                        <button
                                            type="submit"
                                            class="focus:outline-none transition-colors {{ $userRating?->liked ? 'text-red-500' : 'text-gray-300 hover:text-red-400' }}"
                                            title="{{ $userRating?->liked ? 'Unlike' : 'Like' }}"
                                            aria-label="{{ $userRating?->liked ? 'Unlike' : 'Like' }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                {{-- Watchlist buttons --}}
                                <div class="flex items-center gap-3">
                                    @php $listType = $userWatchlistEntry?->list_type; @endphp

                                    {{-- Want to Watch --}}
                                    @if($listType === 'want_to_watch')
                                        <form method="POST" action="{{ route('movies.watchlist.destroy', $movie) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition">
                                                &#10003; Want to Watch
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('movies.watchlist.store', $movie) }}">
                                            @csrf
                                            <input type="hidden" name="list_type" value="want_to_watch">
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                                + Want to Watch
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Watched --}}
                                    @if($listType === 'watched')
                                        <form method="POST" action="{{ route('movies.watchlist.destroy', $movie) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-md bg-green-600 text-white hover:bg-green-700 transition">
                                                &#10003; Watched
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('movies.watchlist.store', $movie) }}">
                                            @csrf
                                            <input type="hidden" name="list_type" value="watched">
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                                + Watched
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @endauth

                            @if($cast->isNotEmpty() || $crew->isNotEmpty())
                            <div x-data="{ tab: 'cast' }" class="mt-6">

                                {{-- Tab bar --}}
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex gap-6">
                                        <button
                                            type="button"
                                            @click="tab = 'cast'"
                                            :class="tab === 'cast'
                                                ? 'border-indigo-500 text-indigo-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                            class="whitespace-nowrap border-b-2 py-3 text-sm font-medium transition-colors">
                                            Cast
                                        </button>
                                        <button
                                            type="button"
                                            @click="tab = 'crew'"
                                            :class="tab === 'crew'
                                                ? 'border-indigo-500 text-indigo-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                            class="whitespace-nowrap border-b-2 py-3 text-sm font-medium transition-colors">
                                            Crew
                                        </button>
                                    </nav>
                                </div>

                                {{-- Cast panel --}}
                                <div x-show="tab === 'cast'" class="pt-4">
                                    @if($cast->isNotEmpty())
                                        <ul class="space-y-1 text-sm">
                                            @foreach($cast as $credit)
                                                <li>
                                                    <a href="{{ $credit->byTypeUrl() }}" class="text-indigo-600 hover:underline">{{ $credit->person->name }}</a>
                                                    @if($credit->character)
                                                        <span class="text-gray-500">as {{ $credit->character }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-400">No cast listed.</p>
                                    @endif
                                </div>

                                {{-- Crew panel --}}
                                <div x-show="tab === 'crew'" class="pt-4">
                                    @if($crew->isNotEmpty())
                                        <dl class="space-y-4">
                                            @foreach($crew as $typeName => $credits)
                                                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                                    <dt class="text-sm font-medium text-gray-500">{{ $typeName }}</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                        <ul class="space-y-1">
                                                            @foreach($credits as $credit)
                                                                <li>
                                                                    <a href="{{ $credit->byTypeUrl() }}" class="text-indigo-600 hover:underline">{{ $credit->person->name }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @else
                                        <p class="text-sm text-gray-400">No crew listed.</p>
                                    @endif
                                </div>

                            </div>
                            @endif

                        </div>{{-- end flex-1 --}}
                    </div>{{-- end flex wrapper --}}

                    {{-- Reviews --}}
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Reviews</h3>

                        @if(session('status') === 'review-saved')
                            <p class="text-xs text-green-600 mb-3">Review saved.</p>
                        @endif
                        @if(session('status') === 'review-deleted')
                            <p class="text-xs text-gray-400 mb-3">Review deleted.</p>
                        @endif

                        <div class="space-y-5">

                            {{-- Current user's reviews (each editable inline) --}}
                            @auth
                            @foreach($userReviews as $userReview)
                            <div x-data="{ editing: false }" class="flex gap-3">
                                <div class="flex-shrink-0">
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
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</span>
                                            @if($userReview->watched_at)
                                                <span class="text-xs text-gray-400">watched {{ $userReview->watched_at->format('j M Y') }}</span>
                                            @endif
                                            <button @click="editing = true"
                                                    class="text-xs text-indigo-500 hover:text-indigo-700 transition underline">
                                                Edit
                                            </button>
                                        </div>
                                        @if($userReview->body)
                                            <p class="text-sm text-gray-700 mt-1 leading-relaxed">{{ $userReview->body }}</p>
                                        @endif
                                    </div>

                                    {{-- Edit form --}}
                                    <div x-show="editing" x-cloak>
                                        <form method="POST" action="{{ route('reviews.update', $userReview) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="mb-2">
                                                <label class="block text-xs text-gray-500 mb-1">Watch date</label>
                                                <input type="date"
                                                       name="watched_at"
                                                       value="{{ old('watched_at', $userReview->watched_at?->format('Y-m-d')) }}"
                                                       class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                            <textarea
                                                name="body"
                                                rows="4"
                                                placeholder="Write your review… (optional)"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >{{ old('body', $userReview->body) }}</textarea>
                                            @error('body')
                                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                            @enderror
                                            <div class="flex items-center gap-3 mt-2">
                                                <button type="submit"
                                                        class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                                    Update
                                                </button>
                                                <button type="button" @click="editing = false"
                                                        class="text-xs text-gray-400 hover:text-gray-600 transition underline">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route('reviews.destroy', $userReview) }}" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition underline">
                                                Delete review
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                            @endforeach

                            {{-- Review or Log form (always available to authenticated users) --}}
                            <div x-data="{ open: false }">
                                <button @click="open = true" x-show="!open"
                                        class="text-sm text-indigo-600 hover:text-indigo-800 transition underline">
                                    + Review or Log
                                </button>
                                <div x-show="open" x-cloak>
                                    <form method="POST" action="{{ route('movies.review.store', $movie) }}">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="block text-xs text-gray-500 mb-1">Watch date</label>
                                            <input type="date"
                                                   name="watched_at"
                                                   value="{{ old('watched_at', now()->format('Y-m-d')) }}"
                                                   class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <textarea
                                            name="body"
                                            rows="4"
                                            placeholder="Write your review… (optional)"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >{{ old('body') }}</textarea>
                                        @error('body')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                        <div class="flex items-center gap-3 mt-2">
                                            <button type="submit"
                                                    class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                                Save
                                            </button>
                                            <button type="button" @click="open = false"
                                                    class="text-xs text-gray-400 hover:text-gray-600 transition underline">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endauth

                            {{-- Public reviews from other users --}}
                            @foreach($reviews as $review)
                            <div class="flex gap-3">
                                <div class="flex-shrink-0">
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
                                    <div class="flex items-baseline gap-2">
                                        <a href="{{ route('profile.show', $review->user->username) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                                            {{ $review->user->name }}
                                        </a>
                                        @if($review->watched_at)
                                            <span class="text-xs text-gray-400">watched {{ $review->watched_at->format('j M Y') }}</span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($review->body)
                                        <p class="text-sm text-gray-700 mt-1 leading-relaxed">{{ $review->body }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach

                            {{-- Empty state (guests only) --}}
                            @guest
                            @if($reviews->isEmpty())
                            <p class="text-sm text-gray-400">No reviews yet. <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Sign in</a> to write one.</p>
                            @endif
                            @endguest

                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ url()->previous() }}" class="text-indigo-600 hover:underline text-sm">&larr; Back</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
