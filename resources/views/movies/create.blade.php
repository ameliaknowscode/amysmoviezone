<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Movie') }}
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

                    <form method="POST" action="{{ route('admin.movies.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="block font-medium text-sm text-gray-700 mb-1">Title</label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="director" class="block font-medium text-sm text-gray-700 mb-1">Director</label>
                            <input type="text" id="director" name="director" value="{{ old('director') }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="release_year" class="block font-medium text-sm text-gray-700 mb-1">Release Year</label>
                            <input type="number" id="release_year" name="release_year" value="{{ old('release_year') }}"
                                min="1888" max="{{ date('Y') + 5 }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div x-data="{ cast: {{ Js::from(old('cast', $initialCast)) }} }" class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Cast</label>
                            <template x-for="(row, i) in cast" :key="i">
                                <div class="flex gap-2 mb-2 max-w-2xl items-center">
                                    <select :name="`cast[${i}][actor_id]`" x-model="row.actor_id"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Select actor…</option>
                                        @foreach($actors as $actor)
                                            <option value="{{ $actor->id }}">{{ $actor->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" :name="`cast[${i}][role]`" x-model="row.role"
                                        placeholder="Role (e.g. Neo)"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                                    <button type="button" @click="cast.splice(i, 1)"
                                        class="text-red-500 hover:text-red-700 text-xl leading-none px-1"
                                        title="Remove">&times;</button>
                                </div>
                            </template>
                            <button type="button" @click="cast.push({ actor_id: '', role: '' })"
                                class="mt-1 text-sm text-blue-600 hover:underline">+ Add actor</button>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                                Add Movie
                            </button>
                            <a href="{{ route('admin.movies.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
