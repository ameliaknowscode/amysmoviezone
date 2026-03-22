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

                    @if($movie->posterUrl())
                    <div class="mb-6">
                        <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }} poster" class="max-w-xs rounded-md shadow-sm">
                    </div>
                    @endif

                    @auth
                    <div class="mb-6 pb-6 border-b border-gray-100">
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
                    </dl>

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

                    <div class="mt-6">
                        <a href="{{ url()->previous() }}" class="text-indigo-600 hover:underline text-sm">&larr; Back</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
