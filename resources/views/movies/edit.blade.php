<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Movie
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if($errors->any())
                        <ul class="mb-4 text-red-600 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('admin.movies.update', $movie) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="title" class="block font-medium text-sm text-gray-700 mb-1">Title</label>
                            <input type="text" id="title" name="title" value="{{ old('title', $movie->title) }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="director" class="block font-medium text-sm text-gray-700 mb-1">Director</label>
                            <input type="text" id="director" name="director" value="{{ old('director', $movie->director) }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="release_year" class="block font-medium text-sm text-gray-700 mb-1">Release Year</label>
                            <input type="number" id="release_year" name="release_year" value="{{ old('release_year', $movie->release_year) }}"
                                min="1888" max="{{ date('Y') + 5 }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        @php $selectedIds = old('actor_ids', $movie->actors->pluck('id')->toArray()); @endphp
                        <div class="mb-4">
                            <label for="actor_ids" class="block font-medium text-sm text-gray-700 mb-1">Cast</label>
                            <select id="actor_ids" name="actor_ids[]" multiple class="tom-select w-full max-w-md">
                                @foreach($actors as $actor)
                                    <option value="{{ $actor->id }}"
                                        {{ in_array($actor->id, $selectedIds) ? 'selected' : '' }}>
                                        {{ $actor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                                Save Changes
                            </button>
                            <a href="{{ route('admin.movies.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
