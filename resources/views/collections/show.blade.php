<x-app-layout>

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-indigo-950 to-indigo-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <a href="{{ route('collections.public.index') }}"
               class="text-xs text-indigo-400 hover:text-indigo-200 transition-colors mb-4 inline-block">&larr; All Collections</a>
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400 mb-2">Collection</p>
                    <h1 class="text-3xl sm:text-4xl font-bold">{{ $collection->name }}</h1>
                    @if($collection->description)
                        <p class="mt-3 text-indigo-200 text-sm max-w-2xl leading-relaxed">{{ $collection->description }}</p>
                    @endif
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-2xl font-bold">{{ $collection->movies->count() }}</p>
                    <p class="text-xs text-indigo-300 uppercase tracking-wide">{{ Str::plural('Film', $collection->movies->count()) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Film grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if($collection->movies->isEmpty())
            <p class="text-center text-gray-400 py-16">No films in this collection yet.</p>
        @else
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                @foreach($collection->movies as $movie)
                <a href="{{ $movie->publicUrl() }}" class="group">
                    <div class="aspect-[2/3] bg-gray-100 rounded-lg overflow-hidden shadow-sm">
                        @if($movie->posterUrl())
                            <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                                 class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                        @else
                            <div class="w-full h-full flex items-center justify-center p-2 text-center">
                                <span class="text-xs text-gray-400 leading-snug">{{ $movie->title }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-1.5">
                        <p class="text-xs font-medium text-gray-800 truncate group-hover:text-indigo-600 transition-colors leading-snug">{{ $movie->title }}</p>
                        <p class="text-xs text-gray-400">{{ $movie->release_year }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

</x-app-layout>
