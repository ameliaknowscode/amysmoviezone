<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $profileUser->name }}'s Lists
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Sub-nav (mirrors profile tabs) --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <nav class="flex overflow-x-auto border-b border-gray-100 px-2">
                    <a href="{{ route('profile.show', $profileUser->username) }}"
                       class="shrink-0 px-4 py-3 text-sm font-medium text-gray-500 hover:text-indigo-600 hover:bg-gray-50 transition-colors border-b-2 border-transparent hover:border-indigo-300">
                        Overview
                    </a>
                    <a href="{{ route('profile.diary', $profileUser->username) }}"
                       class="shrink-0 px-4 py-3 text-sm font-medium text-gray-500 hover:text-indigo-600 hover:bg-gray-50 transition-colors border-b-2 border-transparent hover:border-indigo-300">
                        Diary
                    </a>
                    <a href="{{ route('profile.watchlist', $profileUser->username) }}"
                       class="shrink-0 px-4 py-3 text-sm font-medium text-gray-500 hover:text-indigo-600 hover:bg-gray-50 transition-colors border-b-2 border-transparent hover:border-indigo-300">
                        Watchlist
                    </a>
                    <a href="{{ route('profile.lists', $profileUser->username) }}"
                       class="shrink-0 px-4 py-3 text-sm font-medium text-indigo-600 border-b-2 border-indigo-500">
                        Lists
                    </a>
                </nav>
            </div>

            @if($profileUser->profile_private && !$isOwner)
                <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-400 text-sm">
                    This profile is private.
                </div>
            @elseif($lists->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-400 text-sm">
                    @if($isOwner)
                        You haven't created any lists yet.
                        <a href="{{ route('lists.create') }}" class="text-indigo-600 hover:underline ml-1">Create one</a>
                    @else
                        {{ $profileUser->name }} hasn't created any public lists yet.
                    @endif
                </div>
            @else
                <div class="space-y-3">
                    @foreach($lists as $list)
                        <a href="{{ route('lists.show', $list) }}"
                           class="flex items-center gap-4 bg-white shadow-sm sm:rounded-lg px-5 py-4 hover:shadow-md transition-shadow group">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                                        {{ $list->name }}
                                    </span>
                                    @if($list->is_ranked)
                                        <span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full font-medium">Ranked</span>
                                    @endif
                                    @if(!$list->is_public)
                                        <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-full font-medium">Private</span>
                                    @endif
                                </div>
                                @if($list->description)
                                    <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $list->description }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-sm text-gray-400">
                                {{ $list->items_count }} {{ Str::plural('film', $list->items_count) }}
                            </span>
                        </a>
                    @endforeach
                </div>

                @if($isOwner)
                    <div class="text-center">
                        <a href="{{ route('lists.create') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            New List
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
