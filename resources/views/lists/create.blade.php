<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">New List</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 sm:rounded-lg p-6">
                <form method="POST" action="{{ route('lists.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-300 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="100"
                               class="w-full border-zinc-700 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-zinc-300 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" maxlength="1000"
                                  class="w-full border-zinc-700 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_ranked" value="1" {{ old('is_ranked') ? 'checked' : '' }}
                                   class="rounded border-zinc-700 text-amber-400 shadow-sm focus:ring-amber-500">
                            <span class="text-sm text-zinc-300">Ranked list</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', true) ? 'checked' : '' }}
                                   class="rounded border-zinc-700 text-amber-400 shadow-sm focus:ring-amber-500">
                            <span class="text-sm text-zinc-300">Public</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="px-5 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-400 transition-colors">
                            Create List
                        </button>
                        <a href="{{ route('lists.index') }}" class="text-sm text-zinc-400 hover:text-zinc-300">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
