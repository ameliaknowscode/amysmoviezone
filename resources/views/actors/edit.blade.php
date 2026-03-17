<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Actor
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

                    <form method="POST" action="{{ route('admin.actors.update', $actor) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-gray-700 mb-1">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $actor->name) }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="date_of_birth" class="block font-medium text-sm text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth"
                                value="{{ old('date_of_birth', $actor->date_of_birth) }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="nationality" class="block font-medium text-sm text-gray-700 mb-1">Nationality</label>
                            <input type="text" id="nationality" name="nationality"
                                value="{{ old('nationality', $actor->nationality) }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        @php $selectedMovieIds = old('movie_ids', $actor->movies->pluck('id')->toArray()); @endphp
                        <div class="mb-4">
                            <label for="movie_ids" class="block font-medium text-sm text-gray-700 mb-1">Filmography</label>
                            <select id="movie_ids" name="movie_ids[]" multiple class="tom-select w-full max-w-md">
                                @foreach($movies as $movie)
                                    <option value="{{ $movie->id }}"
                                        {{ in_array($movie->id, $selectedMovieIds) ? 'selected' : '' }}>
                                        {{ $movie->title }} ({{ $movie->release_year }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                                Save Changes
                            </button>
                            <a href="{{ route('admin.actors.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
