<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Recommended for You</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($tooFew)
                {{-- Not enough ratings yet --}}
                <div class="bg-white shadow-sm sm:rounded-lg px-6 py-12 text-center">
                    <p class="text-3xl mb-3">🎬</p>
                    <h3 class="text-base font-semibold text-gray-800 mb-1">Rate a few more films to get started</h3>
                    <p class="text-sm text-gray-500">
                        You've rated {{ $rated }} {{ $rated === 1 ? 'film' : 'films' }}.
                        Rate at least {{ $needed }} to unlock personalised recommendations.
                    </p>
                    <a href="{{ route('movies.browse') }}"
                       class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                        Browse Movies
                    </a>
                </div>

            @elseif($movies->isEmpty())
                {{-- Rated enough but no similar users yet --}}
                <div class="bg-white shadow-sm sm:rounded-lg px-6 py-12 text-center">
                    <p class="text-3xl mb-3">👀</p>
                    <h3 class="text-base font-semibold text-gray-800 mb-1">No recommendations yet</h3>
                    <p class="text-sm text-gray-500">
                        We couldn't find enough members with similar taste yet.
                        Keep rating films and check back soon!
                    </p>
                </div>

            @else
                <p class="text-sm text-gray-500 px-1">
                    Based on members who rated the same films as you.
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-5">
                    @foreach($movies as $movie)
                        <a href="{{ $movie->publicUrl() }}" class="group block">
                            {{-- Poster --}}
                            <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-200 shadow-sm ring-1 ring-black/5">
                                @if($movie->posterUrl())
                                    <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-indigo-100 p-2 text-center">
                                        <span class="text-xs text-indigo-400 leading-snug">{{ $movie->title }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="mt-2 space-y-0.5">
                                <p class="text-xs font-medium text-gray-800 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">
                                    {{ $movie->title }}
                                </p>
                                <div class="flex items-center gap-1.5">
                                    @if($movie->release_year)
                                        <span class="text-xs text-gray-400">{{ $movie->release_year }}</span>
                                    @endif
                                    @if($movie->avg_stars)
                                        <span class="text-xs text-yellow-500 font-medium">★ {{ number_format($movie->avg_stars, 1) }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-indigo-400">
                                    {{ $movie->recommender_count }} {{ $movie->recommender_count === 1 ? 'person' : 'people' }} like you
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
