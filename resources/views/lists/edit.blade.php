<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Edit List</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- List settings --}}
            <div class="bg-zinc-900 sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-zinc-300 mb-4">Settings</h3>
                <form method="POST" action="{{ route('lists.update', $movieList) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-300 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $movieList->name) }}" required maxlength="100"
                               class="w-full border-zinc-700 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-zinc-300 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" maxlength="1000"
                                  class="w-full border-zinc-700 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm">{{ old('description', $movieList->description) }}</textarea>
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_ranked" value="1" {{ $movieList->is_ranked ? 'checked' : '' }}
                                   class="rounded border-zinc-700 text-amber-400 shadow-sm focus:ring-amber-500">
                            <span class="text-sm text-zinc-300">Ranked list</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_public" value="1" {{ $movieList->is_public ? 'checked' : '' }}
                                   class="rounded border-zinc-700 text-amber-400 shadow-sm focus:ring-amber-500">
                            <span class="text-sm text-zinc-300">Public</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <button type="submit"
                                class="px-5 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-400 transition-colors">
                            Save Changes
                        </button>
                        <a href="{{ route('lists.show', $movieList) }}" class="text-sm text-zinc-400 hover:text-zinc-300">Cancel</a>
                    </div>
                </form>
            </div>

            {{-- Film order (drag-and-drop) --}}
            @if($movieList->items->isNotEmpty())
                <div class="bg-zinc-900 sm:rounded-lg p-6"
                     x-data="listReorder('{{ route('lists.movies.reorder', $movieList) }}')">
                    <h3 class="text-sm font-semibold text-zinc-300 mb-4">
                        Films
                        @if($movieList->is_ranked)
                            <span class="text-zinc-400 font-normal ml-1">— drag to reorder</span>
                        @else
                            <span class="text-zinc-400 font-normal ml-1">— drag to reorder</span>
                        @endif
                    </h3>

                    <ul id="sortable-list" class="space-y-2">
                        @foreach($movieList->items as $item)
                            <li class="flex items-center gap-3 bg-zinc-900 rounded-lg px-3 py-2 cursor-grab active:cursor-grabbing"
                                data-id="{{ $item->id }}">
                                {{-- Drag handle --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>

                                @if($movieList->is_ranked)
                                    <span class="rank-number text-xs font-bold text-zinc-400 w-5 text-center shrink-0">{{ $loop->iteration }}</span>
                                @endif

                                <div class="w-8 h-12 bg-zinc-700 rounded overflow-hidden shrink-0">
                                    @if($item->movie->posterUrl())
                                        <img src="{{ $item->movie->posterUrl() }}" class="w-full h-full object-cover" alt="">
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-200 truncate">{{ $item->movie->title }}</p>
                                    @if($item->movie->release_year)
                                        <p class="text-xs text-zinc-400">{{ $item->movie->release_year }}</p>
                                    @endif
                                </div>

                                <form method="POST"
                                      action="{{ route('lists.movies.destroy', [$movieList, $item->movie]) }}"
                                      onsubmit="return confirm('Remove this film?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-zinc-400 hover:text-red-400 transition-colors p-1" aria-label="Remove {{ $item->movie->title }} from list">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Danger zone --}}
            <div class="bg-zinc-900 sm:rounded-lg p-6 border border-red-100">
                <h3 class="text-sm font-semibold text-red-600 mb-3">Delete List</h3>
                <p class="text-sm text-zinc-400 mb-4">This will permanently delete the list and all its items.</p>
                <form method="POST" action="{{ route('lists.destroy', $movieList) }}"
                      onsubmit="return confirm('Delete this list? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Delete List
                    </button>
                </form>
            </div>

        </div>
    </div>

</x-app-layout>
