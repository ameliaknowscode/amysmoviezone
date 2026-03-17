<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Search Results for &ldquo;{{ $query }}&rdquo;
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Movies --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Movies</h3>

                    @if($movies->isEmpty())
                        <p class="text-gray-500">No movies found matching &ldquo;{{ $query }}&rdquo;.</p>
                    @else
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="border border-gray-300 px-4 py-2">Title</th>
                                    <th class="border border-gray-300 px-4 py-2">Director</th>
                                    <th class="border border-gray-300 px-4 py-2">Release Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movies as $movie)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <a href="{{ route('movies.show', $movie) }}" class="text-indigo-600 hover:underline">{{ $movie->title }}</a>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $movie->director }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $movie->release_year }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- Actors --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Actors</h3>

                    @if($actors->isEmpty())
                        <p class="text-gray-500">No actors found matching &ldquo;{{ $query }}&rdquo;.</p>
                    @else
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="border border-gray-300 px-4 py-2">Name</th>
                                    <th class="border border-gray-300 px-4 py-2">Nationality</th>
                                    <th class="border border-gray-300 px-4 py-2">Date of Birth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($actors as $actor)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <a href="{{ route('actors.show', $actor) }}" class="text-indigo-600 hover:underline">{{ $actor->name }}</a>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $actor->nationality ?? '—' }}</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            {{ $actor->date_of_birth ? \Carbon\Carbon::parse($actor->date_of_birth)->format('d M Y') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div>
                <a href="{{ route('home') }}" class="text-indigo-600 hover:underline">&larr; Back to home</a>
            </div>

        </div>
    </div>
</x-app-layout>
