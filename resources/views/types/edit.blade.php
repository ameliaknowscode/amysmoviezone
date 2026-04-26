<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            Edit Type
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

                    <form method="POST" action="{{ route('admin.types.update', $type) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-zinc-300 mb-1">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $type->name) }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="is_crew" value="0">
                                <input type="checkbox" id="is_crew" name="is_crew" value="1"
                                    {{ old('is_crew', $type->is_crew) ? 'checked' : '' }}
                                    class="rounded border-zinc-700 text-amber-400 shadow-sm">
                                <span class="text-sm font-medium text-zinc-300">Crew role</span>
                            </label>
                            <p class="mt-1 text-xs text-zinc-500">Check this if the type represents a crew position (e.g. Director, Producer).</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-4 py-2 bg-zinc-800 text-white rounded-md hover:bg-zinc-700">
                                Save Changes
                            </button>
                            <a href="{{ route('admin.types.index') }}" class="text-zinc-400 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
