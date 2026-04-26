<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            {{ __('Movies') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-6 text-zinc-100">

                    @if(session('success'))
                        <p class="mb-4 text-green-600">{{ session('success') }}</p>
                    @endif

                    <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
                        <form method="GET" action="{{ route('admin.movies.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                            <input type="hidden" name="sort" value="{{ $direction }}">
                            <input type="text" name="search" value="{{ $search }}"
                                placeholder="Filter by title or director…"
                                class="border-zinc-700 rounded-md shadow-sm text-sm w-full sm:w-72">
                            <button type="submit"
                                class="btn-amber px-3 py-1.5 text-sm">
                                Filter
                            </button>
                            @if($search)
                                <a href="{{ route('admin.movies.index', ['sort' => $direction]) }}"
                                   class="text-sm text-zinc-500 hover:underline">Clear</a>
                            @endif
                        </form>
                        <a href="{{ route('admin.movies.import') }}" class="text-amber-400 hover:underline text-sm shrink-0">↑ Import CSV</a>
                        <a href="{{ route('admin.movies.create') }}" class="text-amber-400 hover:underline text-sm shrink-0">+ Add Movie</a>
                    </div>

                    @if($movies->isEmpty())
                        <p>No movies found.</p>
                    @else
                        <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-zinc-800 text-left">
                                    <th class="border border-zinc-700 px-4 py-2">ID</th>
                                    <th class="border border-zinc-700 px-4 py-2">Title</th>
                                    <th class="border border-zinc-700 px-4 py-2">Director</th>
                                    <th class="border border-zinc-700 px-4 py-2">
                                        <a href="{{ route('admin.movies.index', ['sort' => $direction === 'asc' ? 'desc' : 'asc', 'search' => $search]) }}"
                                            class="flex items-center gap-1 hover:underline">
                                            Release Year
                                            @if($direction === 'asc')
                                                <span>↑</span>
                                            @else
                                                <span>↓</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="border border-zinc-700 px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movies as $movie)
                                    <tr class="hover:bg-zinc-800">
                                        <td class="border border-zinc-700 px-4 py-2">{{ $movie->id }}</td>
                                        <td class="border border-zinc-700 px-4 py-2">{{ $movie->title }}</td>
                                        <td class="border border-zinc-700 px-4 py-2">
                                            {{ $movie->credits->map(fn($c) => $c->person->name)->join(', ') ?: '—' }}
                                        </td>
                                        <td class="border border-zinc-700 px-4 py-2">{{ $movie->release_year }}</td>
                                        <td class="border border-zinc-700 px-4 py-2 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <a href="{{ route('admin.movies.edit', $movie) }}" class="text-indigo-500 hover:text-amber-300" title="Edit movie">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                    </svg>
                                                </a>
                                                <form method="POST" action="{{ route('admin.movies.destroy', $movie) }}"
                                                    onsubmit="return confirm('Are you sure you want to delete this movie?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Delete movie">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="3 6 5 6 21 6"/>
                                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                            <path d="M10 11v6"/>
                                                            <path d="M14 11v6"/>
                                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        <div class="mt-4">{{ $movies->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
