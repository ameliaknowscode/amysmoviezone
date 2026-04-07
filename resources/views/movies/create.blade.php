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

                    <form method="POST" action="{{ route('admin.movies.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="block font-medium text-sm text-gray-700 mb-1">Title</label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="release_year" class="block font-medium text-sm text-gray-700 mb-1">Release Year</label>
                            <input type="number" id="release_year" name="release_year" value="{{ old('release_year') }}"
                                min="1888" max="{{ date('Y') + 5 }}"
                                class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4" x-data="{ preview: null }">
                            <label class="block font-medium text-sm text-gray-700 mb-1">Poster</label>
                            <div class="max-w-xs">
                                <label for="poster"
                                    class="flex flex-col items-center justify-center w-full border-2 border-gray-300 border-dashed rounded-md cursor-pointer bg-gray-50 hover:bg-gray-100 transition"
                                    :class="preview ? 'p-2' : 'p-6'">
                                    <template x-if="preview">
                                        <img :src="preview" alt="Poster preview" class="w-full rounded-md shadow-sm">
                                    </template>
                                    <template x-if="!preview">
                                        <div class="flex flex-col items-center gap-2 text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5V19a1 1 0 001 1h16a1 1 0 001-1v-2.5M16 9l-4-4-4 4M12 5v10"/>
                                            </svg>
                                            <span class="text-sm">Click to upload poster</span>
                                            <span class="text-xs text-gray-400">PNG, JPG, GIF up to 2MB</span>
                                        </div>
                                    </template>
                                    <input type="file" id="poster" name="poster" accept="image/*" class="hidden"
                                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                </label>
                                <template x-if="preview">
                                    <button type="button" @click="preview = null; $refs.posterInput.value = ''"
                                        class="mt-1 text-xs text-gray-400 hover:text-gray-600 underline">Remove</button>
                                </template>
                            </div>
                        </div>

                        <div x-data="creditsManager({{ Js::from(old('credits_json') ? json_decode(old('credits_json'), true) : $initialCredits) }}, '{{ route('admin.people.search') }}')" class="mb-4">
                            <input type="hidden" name="credits_json" :value="JSON.stringify(credits)">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Credits</label>
                            <template x-for="(row, i) in credits" :key="i">
                                <div class="flex gap-2 mb-2 max-w-3xl items-center">
                                    <div class="relative flex-1">
                                        <input
                                            type="text"
                                            :value="row.query"
                                            @input="searchPeople(row, $event.target.value)"
                                            @keydown.escape="row.open = false"
                                            @blur="setTimeout(() => row.open = false, 150)"
                                            placeholder="Search person…"
                                            autocomplete="off"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                                        >
                                        <div
                                            x-show="row.open"
                                            class="absolute z-10 left-0 right-0 bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-48 overflow-y-auto"
                                            style="display: none;"
                                        >
                                            <template x-for="person in row.results" :key="person.id">
                                                <button
                                                    type="button"
                                                    @click="selectPerson(row, person)"
                                                    class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50"
                                                    x-text="person.name"
                                                ></button>
                                            </template>
                                        </div>
                                    </div>
                                    <select x-model="row.type_id"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="">Select type…</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" x-model="row.character"
                                        placeholder="Character (optional)"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm text-sm">
                                    <button type="button" @click="removeCredit(i)"
                                        class="text-red-500 hover:text-red-700 text-xl leading-none px-1"
                                        title="Remove">&times;</button>
                                </div>
                            </template>
                            <button type="button" @click="addCredit()"
                                class="mt-1 text-sm text-indigo-600 hover:underline">+ Add credit</button>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
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
