<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Watchlist
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Want to Watch --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Want to Watch</h3>
                    </div>

                    @if($wantToWatch->isEmpty())
                        <div class="py-6 text-center">
                            <p class="text-sm text-gray-400 mb-3">Nothing here yet. Browse movies and add them to your watchlist.</p>
                            <a href="{{ route('movies.browse') }}"
                               class="inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-700 underline underline-offset-2">
                                Browse movies
                            </a>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach($wantToWatch as $entry)
                            <li class="py-3 flex items-center gap-4">
                                {{-- Poster placeholder --}}
                                @if($entry->movie->posterUrl())
                                    <img src="{{ $entry->movie->posterUrl() }}" alt="{{ $entry->movie->title }}"
                                        class="h-[110px] w-[75px] object-cover rounded shrink-0 shadow-sm">
                                @else
                                    <div class="h-[110px] w-[75px] rounded bg-gray-200 shrink-0 flex items-center justify-center text-gray-400 text-xs">
                                        &#127902;
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('movies.show', $entry->movie) }}" class="text-sm font-medium text-indigo-600 hover:underline">
                                        {{ $entry->movie->title }}
                                    </a>
                                    <p class="text-xs text-gray-500">{{ $entry->movie->release_year }}</p>
                                </div>
                                <form method="POST" action="{{ route('movies.watchlist.destroy', $entry->movie) }}"
                                      onsubmit="return confirm('Remove this film from your watchlist?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition"
                                            title="Remove" aria-label="Remove {{ $entry->movie->title }} from watchlist">&#10005;</button>
                                </form>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Watched --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Watched</h3>
                    </div>

                    @if($watched->isEmpty())
                        <div class="py-6 text-center">
                            <p class="text-sm text-gray-400 mb-3">Nothing here yet. Mark movies as watched from their pages.</p>
                            <a href="{{ route('movies.browse') }}"
                               class="inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-700 underline underline-offset-2">
                                Browse movies
                            </a>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach($watched as $entry)
                            @php $rating = $ratings[$entry->movie_id] ?? null; @endphp
                            <li class="py-3 flex items-center gap-4">
                                {{-- Poster placeholder --}}
                                @if($entry->movie->posterUrl())
                                    <img src="{{ $entry->movie->posterUrl() }}" alt="{{ $entry->movie->title }}"
                                        class="h-[110px] w-[75px] object-cover rounded shrink-0 shadow-sm">
                                @else
                                    <div class="h-[110px] w-[75px] rounded bg-gray-200 shrink-0 flex items-center justify-center text-gray-400 text-xs">
                                        &#127902;
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('movies.show', $entry->movie) }}" class="text-sm font-medium text-indigo-600 hover:underline">
                                        {{ $entry->movie->title }}
                                    </a>
                                    <p class="text-xs text-gray-500">{{ $entry->movie->release_year }}</p>
                                    @if($entry->watched_at)
                                        <p class="text-xs text-gray-400 mt-0.5">Watched {{ $entry->watched_at->format('j M Y') }}</p>
                                    @endif
                                    @if($rating)
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($rating->stars)
                                        <x-star-display :value="$rating->stars" class="text-sm" />
                                        @endif
                                        @if($rating->liked)
                                        <span class="text-red-500 text-sm">&#10084;</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('movies.watchlist.destroy', $entry->movie) }}"
                                      onsubmit="return confirm('Remove this film from your watchlist?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition"
                                            title="Remove" aria-label="Remove {{ $entry->movie->title }} from watchlist">&#10005;</button>
                                </form>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
