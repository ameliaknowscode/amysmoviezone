<x-app-layout>
    <div class="bg-gradient-to-br from-indigo-950 to-indigo-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400 mb-2">Discover</p>
            <h1 class="text-4xl font-bold">Collections</h1>
            <p class="mt-2 text-indigo-300 text-sm">Curated groupings of films beyond genres.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if($collections->isEmpty())
            <p class="text-gray-500 text-center py-16">No collections have been created yet.</p>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($collections as $collection)
                <a href="{{ route('collections.public.show', $collection->slug) }}"
                   class="group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">

                    {{-- Mini poster strip --}}
                    @php $previewMovies = $collection->movies()->whereNotNull('poster')->limit(4)->get(); @endphp
                    @if($previewMovies->isNotEmpty())
                    <div class="flex h-24 overflow-hidden">
                        @foreach($previewMovies as $movie)
                        <div class="flex-1 overflow-hidden">
                            <img src="{{ $movie->posterUrl() }}" alt=""
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        @endforeach
                        @for($i = $previewMovies->count(); $i < 4; $i++)
                        <div class="flex-1 bg-indigo-50"></div>
                        @endfor
                    </div>
                    @else
                    <div class="h-24 bg-indigo-50"></div>
                    @endif

                    <div class="p-5">
                        <h2 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $collection->name }}</h2>
                        @if($collection->description)
                            <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $collection->description }}</p>
                        @endif
                        <p class="mt-3 text-xs text-gray-400">{{ $collection->movies_count }} {{ Str::plural('film', $collection->movies_count) }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
