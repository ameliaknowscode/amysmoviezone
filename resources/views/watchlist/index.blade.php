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
                        <p class="text-sm text-gray-400">Nothing here yet. Add movies from their pages.</p>
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
                                <form method="POST" action="{{ route('movies.watchlist.destroy', $entry->movie) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition" title="Remove">&#10005;</button>
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
                        <p class="text-sm text-gray-400">Nothing here yet. Mark movies as watched from their pages.</p>
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
                                    @if($rating)
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($rating->stars)
                                        <span class="text-sm text-yellow-400">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="{{ $i <= $rating->stars ? 'text-yellow-400' : 'text-gray-300' }}">&#9733;</span>
                                            @endfor
                                        </span>
                                        @endif
                                        @if($rating->liked)
                                        <span class="text-red-500 text-sm">&#10084;</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('movies.watchlist.destroy', $entry->movie) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition" title="Remove">&#10005;</button>
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
