<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            <a href="{{ route('profile.show', $profileUser->username) }}" class="hover:text-amber-400 transition-colors">{{ $profileUser->name }}</a>'s Watchlist
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Want to Watch --}}
            @if($wantToWatch !== null)
            <div id="want-to-watch" class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-4">Want to Watch</h3>

                    @if($wantToWatch->isEmpty())
                        <p class="text-sm text-zinc-400">Nothing here yet.</p>
                    @else
                        <ul class="divide-y divide-zinc-800">
                            @foreach($wantToWatch as $entry)
                            <li class="py-3 flex items-center gap-4">
                                @if($entry->movie->posterUrl())
                                    <img src="{{ $entry->movie->posterUrl() }}" alt="{{ $entry->movie->title }}"
                                        class="h-[110px] w-[75px] object-cover rounded shrink-0 shadow-sm">
                                @else
                                    <div class="h-[110px] w-[75px] rounded bg-zinc-700 shrink-0 flex items-center justify-center text-zinc-400 text-xs">
                                        &#127902;
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $entry->movie->publicUrl() }}" class="text-sm font-medium text-amber-400 hover:underline">
                                        {{ $entry->movie->title }}
                                    </a>
                                    <p class="text-xs text-zinc-400">{{ $entry->movie->release_year }}</p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @if($wantToWatch->hasPages())
                            <div class="mt-4">{{ $wantToWatch->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
            @endif

            {{-- Watched --}}
            @if($watched !== null)
            <div id="watched" class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-4">Watched</h3>

                    @if($watched->isEmpty())
                        <p class="text-sm text-zinc-400">Nothing here yet.</p>
                    @else
                        <ul class="divide-y divide-zinc-800">
                            @foreach($watched as $entry)
                            <li class="py-3 flex items-center gap-4">
                                @if($entry->movie->posterUrl())
                                    <img src="{{ $entry->movie->posterUrl() }}" alt="{{ $entry->movie->title }}"
                                        class="h-[110px] w-[75px] object-cover rounded shrink-0 shadow-sm">
                                @else
                                    <div class="h-[110px] w-[75px] rounded bg-zinc-700 shrink-0 flex items-center justify-center text-zinc-400 text-xs">
                                        &#127902;
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $entry->movie->publicUrl() }}" class="text-sm font-medium text-amber-400 hover:underline">
                                        {{ $entry->movie->title }}
                                    </a>
                                    <p class="text-xs text-zinc-400">{{ $entry->movie->release_year }}</p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @if($watched->hasPages())
                            <div class="mt-4">{{ $watched->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
            @endif

            @if($wantToWatch === null && $watched === null)
            <div class="card">
                <div class="p-6 text-sm text-zinc-400">This user's watchlists are private.</div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
