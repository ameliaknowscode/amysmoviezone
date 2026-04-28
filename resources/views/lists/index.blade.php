<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-zinc-100 leading-tight">My Lists</h2>
            <a href="{{ route('lists.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-400 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($lists->isEmpty())
                <div class="bg-zinc-900 sm:rounded-lg p-10 text-center text-zinc-400">
                    <p class="text-sm">You haven't created any lists yet.</p>
                    <a href="{{ route('lists.create') }}" class="mt-3 inline-block text-sm text-amber-400 hover:underline">Create your first list</a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($lists as $list)
                        <a href="{{ route('lists.show', $list) }}"
                           class="flex items-center gap-4 bg-zinc-900 sm:rounded-lg px-5 py-4 hover:shadow-md transition-shadow group">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-zinc-100 group-hover:text-amber-400 transition-colors truncate">
                                        {{ $list->name }}
                                    </span>
                                    @if($list->is_ranked)
                                        <span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full font-medium">Ranked</span>
                                    @endif
                                    @if(!$list->is_public)
                                        <span class="text-xs bg-zinc-800 text-zinc-400 px-1.5 py-0.5 rounded-full font-medium">Private</span>
                                    @endif
                                </div>
                                @if($list->description)
                                    <p class="text-sm text-zinc-400 mt-0.5 truncate">{{ $list->description }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-sm text-zinc-400">
                                {{ $list->items_count }} {{ Str::plural('film', $list->items_count) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
