<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Genre</h2>
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

                    <form method="POST" action="{{ route('admin.genres.update', $genre) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-gray-700 mb-1">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $genre->name) }}"
                                   class="w-full max-w-md border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Save Changes
                            </button>
                            <a href="{{ route('admin.genres.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
