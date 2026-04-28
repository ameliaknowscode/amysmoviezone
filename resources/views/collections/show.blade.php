<x-app-layout>

    {{-- Hero --}}
    <div class="bg-zinc-900 text-white border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <a href="{{ route('collections.public.index') }}"
               class="text-xs text-amber-400 hover:text-amber-300 transition-colors mb-4 inline-block">&larr; All Collections</a>
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-400 mb-2">Collection</p>
                    <h1 class="text-3xl sm:text-4xl font-bold">{{ $collection->name }}</h1>
                    @if($collection->description)
                        <p class="mt-3 text-zinc-400 text-sm max-w-2xl leading-relaxed">{{ $collection->description }}</p>
                    @endif
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-2xl font-bold">{{ $collection->movies->count() }}</p>
                    <p class="text-xs text-zinc-400 uppercase tracking-wide">{{ Str::plural('Film', $collection->movies->count()) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Film grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if($collection->movies->isEmpty())
            <p class="text-center text-zinc-400 py-16">No films in this collection yet.</p>
        @else
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                @foreach($collection->movies as $movie)
                <x-movie-poster-card :movie="$movie" />
                @endforeach
            </div>
        @endif
    </div>

</x-app-layout>
