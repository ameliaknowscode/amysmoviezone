@props(['movie', 'userWatchlistEntry', 'userLists', 'movieListIds'])

@php $listType = $userWatchlistEntry?->list_type; @endphp

<div class="flex flex-wrap items-center gap-2">
    {{-- Want to Watch --}}
    @if($listType === 'want_to_watch')
        <form method="POST" action="{{ route('movies.watchlist.destroy', $movie) }}">
            @csrf @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md bg-amber-500 text-white hover:bg-amber-900/200 transition font-medium">
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Want to Watch
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('movies.watchlist.store', $movie) }}">
            @csrf
            <input type="hidden" name="list_type" value="want_to_watch">
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-zinc-600 text-zinc-300 hover:border-zinc-400 hover:text-white transition font-medium">
                + Want to Watch
            </button>
        </form>
    @endif

    {{-- Watched --}}
    @if($listType === 'watched')
        <div x-data="{ editing: false }">
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('movies.watchlist.destroy', $movie) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md bg-emerald-600 text-white hover:bg-emerald-500 transition font-medium">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Watched
                    </button>
                </form>
                <button @click="editing = !editing"
                        class="text-xs text-zinc-400 hover:text-white transition">
                    @if($userWatchlistEntry->watched_at)
                        {{ $userWatchlistEntry->watched_at->format('j M Y') }}
                    @else
                        + add date
                    @endif
                </button>
            </div>
            <form x-show="editing" x-cloak method="POST"
                  action="{{ route('movies.watchlist.watched-at', $movie) }}"
                  class="flex items-center gap-2 mt-2">
                @csrf @method('PATCH')
                <input type="date" name="watched_at"
                       value="{{ $userWatchlistEntry->watched_at?->format('Y-m-d') }}"
                       max="{{ now()->format('Y-m-d') }}"
                       class="text-xs rounded border border-zinc-700 bg-zinc-800 text-zinc-100 px-2 py-1 focus:outline-none focus:border-amber-500">
                <button type="submit" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition">Save</button>
                <button type="button" @click="editing = false" class="text-xs text-zinc-500 hover:text-zinc-300 transition">Cancel</button>
            </form>
        </div>
    @else
        <div x-data="{ open: false }">
            <button @click="open = true"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-zinc-600 text-zinc-300 hover:border-zinc-400 hover:text-white transition font-medium">
                + Watched
            </button>
            <form x-show="open" x-cloak method="POST"
                  action="{{ route('movies.watchlist.store', $movie) }}"
                  class="flex items-center gap-2 mt-2">
                @csrf
                <input type="hidden" name="list_type" value="watched">
                <input type="date" name="watched_at"
                       value="{{ now()->format('Y-m-d') }}"
                       max="{{ now()->format('Y-m-d') }}"
                       class="text-xs rounded border border-zinc-700 bg-zinc-800 text-zinc-100 px-2 py-1 focus:outline-none focus:border-amber-500">
                <button type="submit" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition">Mark Watched</button>
                <button type="button" @click="open = false" class="text-xs text-zinc-500 hover:text-zinc-300 transition">Cancel</button>
            </form>
        </div>
    @endif

    <a href="#review-form" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-zinc-400 hover:text-white transition">
        + Review or Log
    </a>

    {{-- Lists --}}
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button @click="open = !open"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-zinc-600 text-zinc-300 hover:bg-zinc-800 transition font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h8"/>
            </svg>
            Lists
        </button>
        <div x-show="open" x-cloak
             class="absolute left-0 mt-1.5 w-56 bg-zinc-900 rounded-lg shadow-lg border border-zinc-800 py-1 z-20">
            @forelse($userLists as $list)
                @php $inList = $movieListIds->contains($list->id); @endphp
                <form method="POST"
                      action="{{ $inList
                          ? route('lists.movies.destroy', [$list, $movie])
                          : route('lists.movies.store', $list) }}">
                    @csrf
                    @if($inList) @method('DELETE') @else
                        <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                    @endif
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800 transition-colors text-left">
                        <span class="w-4 h-4 shrink-0 flex items-center justify-center">
                            @if($inList)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            @endif
                        </span>
                        <span class="truncate">{{ $list->name }}</span>
                    </button>
                </form>
            @empty
                <p class="px-4 py-2 text-xs text-zinc-500">No lists yet.</p>
            @endforelse
            <div class="border-t border-zinc-800 mt-1 pt-1">
                <a href="{{ route('lists.create') }}"
                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-amber-400 hover:bg-zinc-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New list
                </a>
            </div>
        </div>
    </div>
</div>
