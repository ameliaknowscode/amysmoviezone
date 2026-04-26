<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            {{ __('Add Person') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 overflow-hidden sm:rounded-lg">
                <div class="p-6 text-zinc-100">

                    @if($errors->any())
                        <ul class="mb-4 text-red-600 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('admin.people.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-zinc-300 mb-1">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="date_of_birth" class="block font-medium text-sm text-zinc-300 mb-1">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="date_of_death" class="block font-medium text-sm text-zinc-300 mb-1">Date of Death</label>
                            <input type="date" id="date_of_death" name="date_of_death" value="{{ old('date_of_death') }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="nationality" class="block font-medium text-sm text-zinc-300 mb-1">Nationality</label>
                            <input type="text" id="nationality" name="nationality" value="{{ old('nationality') }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="bio" class="block font-medium text-sm text-zinc-300 mb-1">Bio</label>
                            <textarea id="bio" name="bio" rows="5"
                                class="w-full max-w-2xl border-zinc-700 rounded-md shadow-sm text-sm">{{ old('bio') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block font-medium text-sm text-zinc-300 mb-1">Photo</label>
                            <input type="file" name="photo" accept="image/*"
                                   class="text-sm text-zinc-400 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-amber-900/20 file:text-amber-300 hover:file:bg-amber-900/30">
                            <p class="text-xs text-zinc-500 mt-1">JPG, PNG, WebP — max 2MB</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-400">
                                Add Person
                            </button>
                            <a href="{{ route('admin.people.index') }}" class="text-zinc-400 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
