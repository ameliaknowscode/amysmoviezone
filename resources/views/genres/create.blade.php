<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Add Genre</h2>
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

                    <form method="POST" action="{{ route('admin.genres.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-zinc-300 mb-1">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                    class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-400">
                                Add Genre
                            </button>
                            <a href="{{ route('admin.genres.index') }}" class="text-zinc-400 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
