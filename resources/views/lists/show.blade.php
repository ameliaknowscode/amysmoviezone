<x-app-layout :title="$movieList->name">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="font-semibold text-xl text-zinc-100 leading-tight truncate">{{ $movieList->name }}</h2>
                    @if($movieList->is_ranked)
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium">Ranked</span>
                    @endif
                    @if(!$movieList->is_public)
                        <span class="text-xs bg-zinc-800 text-zinc-400 px-2 py-0.5 rounded-full font-medium">Private</span>
                    @endif
                </div>
                <p class="text-sm text-zinc-400 mt-0.5">
                    by <a href="{{ route('profile.show', $movieList->user->username) }}" class="hover:text-amber-400 transition-colors">{{ $movieList->user->name }}</a>
                    · {{ $movieList->items->count() }} {{ Str::plural('film', $movieList->items->count()) }}
                    @if($followerCount > 0)
                        · {{ $followerCount }} {{ Str::plural('follower', $followerCount) }}
                    @endif
                </p>
            </div>
            @auth
                <div class="flex items-center gap-2 shrink-0">
                    @if(auth()->id() === $movieList->user_id)
                        <a href="{{ route('lists.edit', $movieList) }}"
                           class="px-4 py-2 border border-zinc-700 text-sm font-medium text-zinc-300 rounded-lg hover:bg-zinc-800 transition-colors">
                            Edit List
                        </a>
                    @elseif($movieList->is_public)
                        @if($isFollowing)
                            <form method="POST" action="{{ route('lists.unfollow', $movieList) }}">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 border border-zinc-700 text-sm font-medium text-zinc-300 rounded-lg hover:bg-zinc-800 transition-colors">
                                    Following
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('lists.follow', $movieList) }}">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-400 transition-colors">
                                    Follow List
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($movieList->description)
                <div class="bg-zinc-900 sm:rounded-lg px-6 py-4 text-sm text-zinc-400 leading-relaxed">
                    {{ $movieList->description }}
                </div>
            @endif

            @if($movieList->items->isEmpty())
                <div class="bg-zinc-900 sm:rounded-lg p-10 text-center text-zinc-400 text-sm">
                    No films in this list yet.
                </div>
            @else
                <div class="bg-zinc-900 sm:rounded-lg divide-y divide-zinc-800">
                    @foreach($movieList->items as $item)
                        <a href="{{ $item->movie->publicUrl() }}"
                           class="flex items-center gap-4 px-5 py-3 hover:bg-zinc-800 transition-colors group">

                            {{-- Rank / position --}}
                            @if($movieList->is_ranked)
                                <span class="w-7 text-center text-sm font-bold text-zinc-400 shrink-0">{{ $loop->iteration }}</span>
                            @endif

                            {{-- Poster --}}
                            <div class="w-10 h-[60px] bg-zinc-700 rounded overflow-hidden shadow-sm shrink-0">
                                @if($item->movie->posterUrl())
                                    <img src="{{ $item->movie->posterUrl() }}" alt="{{ $item->movie->title }}"
                                         class="w-full h-full object-cover">
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-zinc-100 group-hover:text-amber-400 transition-colors truncate">
                                    {{ $item->movie->title }}
                                </p>
                                @if($item->movie->release_year)
                                    <p class="text-sm text-zinc-400">{{ $item->movie->release_year }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
