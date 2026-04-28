<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            Edit Person
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-6 text-zinc-100">

                    @if($errors->any())
                        <ul class="mb-4 text-red-600 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('admin.people.update', $person) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-zinc-300 mb-1">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $person->name) }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="date_of_birth" class="block font-medium text-sm text-zinc-300 mb-1">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth"
                                value="{{ old('date_of_birth', $person->date_of_birth) }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="date_of_death" class="block font-medium text-sm text-zinc-300 mb-1">Date of Death</label>
                            <input type="date" id="date_of_death" name="date_of_death"
                                value="{{ old('date_of_death', $person->date_of_death) }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="nationality" class="block font-medium text-sm text-zinc-300 mb-1">Nationality</label>
                            <input type="text" id="nationality" name="nationality"
                                value="{{ old('nationality', $person->nationality) }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="bio" class="block font-medium text-sm text-zinc-300 mb-1">Bio</label>
                            <textarea id="bio" name="bio" rows="5"
                                class="w-full max-w-2xl border-zinc-700 rounded-md shadow-sm text-sm">{{ old('bio', $person->bio) }}</textarea>
                        </div>

                        <div class="mb-4" x-data="{ removing: false }">
                            <label class="block font-medium text-sm text-zinc-300 mb-1">Photo</label>
                            @if($person->photo)
                                <div class="flex items-start gap-4 mb-2">
                                    <img src="{{ asset('storage/' . $person->photo) }}" alt="{{ $person->name }}"
                                         class="w-24 h-24 object-cover rounded-md shadow-sm">
                                    <div>
                                        <label class="flex items-center gap-2 text-sm text-red-600 cursor-pointer">
                                            <input type="checkbox" name="remove_photo" value="1" x-model="removing"
                                                   class="rounded border-zinc-700 text-red-600">
                                            Remove current photo
                                        </label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="photo" accept="image/*"
                                   x-bind:disabled="removing"
                                   class="text-sm text-zinc-400 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-amber-900/20 file:text-amber-300 hover:file:bg-amber-900/30">
                            <p class="text-xs text-zinc-400 mt-1">JPG, PNG, WebP — max 2MB</p>
                        </div>

                        <div x-data="{ credits: {{ Js::from(old('credits', $initialCredits)) }} }" class="mb-4">
                            <label class="block font-medium text-sm text-zinc-300 mb-2">Credits</label>
                            <template x-for="(row, i) in credits" :key="i">
                                <div class="flex gap-2 mb-2 max-w-3xl items-center">
                                    <select :name="`credits[${i}][movie_id]`" x-model="row.movie_id"
                                        class="flex-1 border-zinc-700 rounded-md shadow-sm text-sm">
                                        <option value="">Select movie…</option>
                                        @foreach($movies as $movie)
                                            <option value="{{ $movie->id }}">{{ $movie->title }} ({{ $movie->release_year }})</option>
                                        @endforeach
                                    </select>
                                    <select :name="`credits[${i}][type_id]`" x-model="row.type_id"
                                        class="flex-1 border-zinc-700 rounded-md shadow-sm text-sm">
                                        <option value="">Select type…</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" :name="`credits[${i}][character]`" x-model="row.character"
                                        placeholder="Character (optional)"
                                        class="flex-1 border-zinc-700 rounded-md shadow-sm text-sm">
                                    <button type="button" @click="credits.splice(i, 1)"
                                        class="text-red-500 hover:text-red-700 text-xl leading-none px-1"
                                        title="Remove">&times;</button>
                                </div>
                            </template>
                            <button type="button" @click="credits.push({ movie_id: '', type_id: '', character: '' })"
                                class="mt-1 text-sm text-amber-400 hover:underline">+ Add credit</button>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="btn-amber px-4 py-2">
                                Save Changes
                            </button>
                            <a href="{{ route('admin.people.index') }}" class="text-zinc-400 hover:underline">Cancel</a>
                            <a href="{{ route('admin.people.credits.import', $person) }}"
                               class="text-amber-400 hover:underline text-sm ml-auto">↑ Import Credits from CSV</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
