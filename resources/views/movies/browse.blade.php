<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Movies
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($movies->isEmpty())
                <p class="text-sm text-gray-500">No movies yet.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($movies as $movie)
                    <a href="{{ $movie->publicUrl() }}" class="group relative">
                        {{-- Hover tooltip --}}
                        <div class="absolute bottom-[calc(100%-4px)] left-1/2 -translate-x-1/2 z-10 mb-2 w-max max-w-[200px] px-2.5 py-1.5 bg-gray-900 text-white text-xs rounded shadow-lg pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-150 text-center leading-snug">
                            {{ $movie->title }}{{ $movie->release_year ? ' (' . $movie->release_year . ')' : '' }} {{ $movie->ratings_avg_stars ? '★ ' . number_format($movie->ratings_avg_stars, 1) : 'Unrated' }}
                        </div>
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

                @if($movies->hasPages())
                <div class="mt-8">{{ $movies->links() }}</div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
