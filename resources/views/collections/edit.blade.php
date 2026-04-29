<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Edit Collection</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Settings --}}
            <div class="card p-6">

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-md text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.collections.update', $collection) }}" class="space-y-5">
                    @csrf @method('PATCH')

                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-300 mb-1">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $collection->name) }}"
                               class="w-full max-w-md rounded-md border-zinc-700 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm"
                               required>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-zinc-300 mb-1">Description <span class="text-zinc-400 font-normal">(optional)</span></label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full rounded-md border-zinc-700 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">{{ old('description', $collection->description) }}</textarea>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit"
                                class="btn-amber px-4 py-2 text-sm">
                            Save Changes
                        </button>
                        <a href="{{ route('admin.collections.index') }}" class="text-sm text-zinc-400 hover:text-zinc-300">Cancel</a>
                    </div>
                </form>

            </div>

            {{-- Add a film (search) --}}
            <div class="card p-6"
                 x-data="collectionMovieSearch('{{ route('admin.collections.movies.search', $collection) }}')">
                <h3 class="text-sm font-semibold text-zinc-300 mb-4">Add a Film</h3>

                <div class="relative" @click.outside="open = false">
                    <label for="movie-search" class="sr-only">Search films</label>
                    <input type="text" id="movie-search"
                           x-model="query"
                           @input.debounce.250ms="search()"
                           @focus="if (results.length) open = true"
                           placeholder="Search by title&hellip;"
                           autocomplete="off"
                           class="w-full input-dark rounded-md sm:text-sm">

                    <ul x-show="open && results.length" x-cloak
                        class="absolute z-10 mt-1 w-full bg-zinc-900 border border-zinc-700 rounded-md shadow-lg max-h-64 overflow-y-auto">
                        <template x-for="movie in results" :key="movie.id">
                            <li>
                                <form method="POST" action="{{ route('admin.collections.movies.attach', $collection) }}">
                                    @csrf
                                    <input type="hidden" name="movie_id" :value="movie.id">
                                    <button type="submit"
                                            class="w-full text-left px-3 py-2 text-sm text-zinc-200 hover:bg-zinc-800 flex justify-between items-center">
                                        <span x-text="movie.title"></span>
                                        <span class="text-xs text-zinc-400" x-text="movie.release_year"></span>
                                    </button>
                                </form>
                            </li>
                        </template>
                    </ul>
                </div>

                <p x-show="query.length >= 2 && !results.length && !loading" x-cloak
                   class="text-xs text-zinc-400 mt-2">No matching films.</p>
            </div>

            {{-- Films list (drag-and-drop) --}}
            @if($collection->movies->isNotEmpty())
                <div class="card p-6"
                     x-data="collectionReorder('{{ route('admin.collections.reorder', $collection) }}')">
                    <h3 class="text-sm font-semibold text-zinc-300 mb-4">
                        Films
                        <span class="text-zinc-400 font-normal ml-1">— drag to reorder</span>
                    </h3>

                    <ul id="sortable-collection" class="space-y-2">
                        @foreach($collection->movies as $movie)
                            <li class="flex items-center gap-3 bg-zinc-900 rounded-lg px-3 py-2 cursor-grab active:cursor-grabbing"
                                data-id="{{ $movie->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>

                                <div class="w-8 h-12 bg-zinc-700 rounded overflow-hidden shrink-0">
                                    @if($movie->posterUrl())
                                        <img src="{{ $movie->posterUrl() }}" class="w-full h-full object-cover" alt="">
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-200 truncate">{{ $movie->title }}</p>
                                    @if($movie->release_year)
                                        <p class="text-xs text-zinc-400">{{ $movie->release_year }}</p>
                                    @endif
                                </div>

                                <form method="POST"
                                      action="{{ route('admin.collections.movies.detach', [$collection, $movie]) }}"
                                      onsubmit="return confirm('Remove this film from the collection?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-zinc-400 hover:text-red-400 transition-colors p-1" aria-label="Remove {{ $movie->title }} from collection">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>

    {{-- SortableJS --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('collectionReorder', (reorderUrl) => ({
                init() {
                    const el = document.getElementById('sortable-collection');
                    if (!el) return;

                    Sortable.create(el, {
                        animation: 150,
                        handle: 'li',
                        onEnd: () => {
                            const order = [...el.querySelectorAll('li[data-id]')].map(li => li.dataset.id);

                            fetch(reorderUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ order }),
                            });
                        },
                    });
                },
            }));

            Alpine.data('collectionMovieSearch', (searchUrl) => ({
                query: '',
                results: [],
                open: false,
                loading: false,

                async search() {
                    if (this.query.trim().length < 2) {
                        this.results = [];
                        this.open = false;
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch(`${searchUrl}?q=${encodeURIComponent(this.query)}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        this.results = await res.json();
                        this.open = true;
                    } finally {
                        this.loading = false;
                    }
                },
            }));
        });
    </script>
</x-app-layout>
