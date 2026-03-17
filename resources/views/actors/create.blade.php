<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Actor') }}
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

                    <form method="POST" action="{{ route('admin.actors.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-gray-700 mb-1">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="date_of_birth" class="block font-medium text-sm text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="nationality" class="block font-medium text-sm text-gray-700 mb-1">Nationality</label>
                            <input type="text" id="nationality" name="nationality" value="{{ old('nationality') }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div x-data="{ filmography: {{ Js::from(old('filmography', $initialFilmography)) }} }" class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Filmography</label>
                            <template x-for="(row, i) in filmography" :key="i">
                                <div class="flex gap-2 mb-2 max-w-2xl items-center">
                                    <select :name="`filmography[${i}][movie_id]`" x-model="row.movie_id"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Select movie…</option>
                                        @foreach($movies as $movie)
                                            <option value="{{ $movie->id }}">{{ $movie->title }} ({{ $movie->release_year }})</option>
                                        @endforeach
                                    </select>
                                    <input type="text" :name="`filmography[${i}][role]`" x-model="row.role"
                                        placeholder="Role (e.g. Neo)"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                                    <button type="button" @click="filmography.splice(i, 1)"
                                        class="text-red-500 hover:text-red-700 text-xl leading-none px-1"
                                        title="Remove">&times;</button>
                                </div>
                            </template>
                            <button type="button" @click="filmography.push({ movie_id: '', role: '' })"
                                class="mt-1 text-sm text-blue-600 hover:underline">+ Add movie</button>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                                Add Actor
                            </button>
                            <a href="{{ route('admin.actors.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
