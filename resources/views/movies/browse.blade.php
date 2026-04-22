<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Movies</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('movies.browse') }}"
                  class="bg-zinc-900 sm:rounded-lg px-4 py-4 flex flex-wrap gap-3 items-end">

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Title</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search titles…"
                           class="w-full rounded-md border-zinc-700 bg-zinc-800 text-zinc-100 placeholder-zinc-500 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Director</label>
                    <input type="text" name="director" value="{{ $director }}" placeholder="e.g. Wes Anderson"
                           class="w-full rounded-md border-zinc-700 bg-zinc-800 text-zinc-100 placeholder-zinc-500 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Genre</label>
                    <select name="genre"
                            class="w-full rounded-md border-zinc-700 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Any</option>
                        @foreach($genres as $g)
                            <option value="{{ $g->slug }}" {{ $genre === $g->slug ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">From year</label>
                    <select name="year_from"
                            class="rounded-md border-zinc-700 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Any</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $yearFrom == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">To year</label>
                    <select name="year_to"
                            class="rounded-md border-zinc-700 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Any</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $yearTo == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-500 mb-1">Sort by</label>
                    <select name="sort"
                            class="rounded-md border-zinc-700 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="rating"       {{ $sort === 'rating'       ? 'selected' : '' }}>Highest Rated</option>
                        <option value="title_asc"    {{ $sort === 'title_asc'    ? 'selected' : '' }}>Title A–Z</option>
                        <option value="year_desc"    {{ $sort === 'year_desc'    ? 'selected' : '' }}>Newest First</option>
                        <option value="year_asc"     {{ $sort === 'year_asc'     ? 'selected' : '' }}>Oldest First</option>
                        <option value="most_watched" {{ $sort === 'most_watched' ? 'selected' : '' }}>Most Watched</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-md hover:bg-amber-400 transition-colors">
                        Filter
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('movies.browse') }}"
                           class="px-4 py-2 bg-zinc-800 text-zinc-400 text-sm font-medium rounded-md hover:bg-zinc-700 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

            </form>

            {{-- Results --}}
            @if($movies->isEmpty())
                <p class="text-sm text-zinc-500 px-1">No movies matched your filters.</p>
            @else
                @if($hasFilters)
                    <p class="text-sm text-zinc-500 px-1">
                        {{ number_format($movies->total()) }} {{ Str::plural('film', $movies->total()) }} found
                    </p>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($movies as $movie)
                        <a href="{{ $movie->publicUrl() }}" class="group relative">
                            {{-- Hover tooltip --}}
                            <div class="absolute bottom-[calc(100%-4px)] left-1/2 -translate-x-1/2 z-10 mb-2 w-max max-w-[200px] px-2.5 py-1.5 bg-zinc-900 text-white text-xs rounded shadow-lg pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-150 text-center leading-snug">
                                {{ $movie->title }}{{ $movie->release_year ? ' (' . $movie->release_year . ')' : '' }}
                                {{ $movie->ratings_avg_stars ? '★ ' . number_format($movie->ratings_avg_stars, 1) : 'Unrated' }}
                            </div>
                            <div class="aspect-[2/3] bg-zinc-700 rounded-md overflow-hidden shadow-sm ring-1 ring-zinc-700">
                                @if($movie->posterUrl())
                                    <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                                         class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                @else
                                    <div class="w-full h-full flex items-center justify-center p-2 text-center">
                                        <span class="text-xs text-zinc-500 leading-snug">{{ $movie->title }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-1.5">
                                <div class="text-sm font-medium text-zinc-100 truncate group-hover:text-amber-400 transition-colors">
                                    {{ $movie->title }}
                                </div>
                                @if($movie->release_year)
                                    <div class="text-xs text-zinc-500">{{ $movie->release_year }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($movies->hasPages())
                    <div class="mt-8">{{ $movies->links() }}</div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
