<a href="{{ $rating->movie->publicUrl() }}" class="group">
    <div class="aspect-[2/3] bg-zinc-800 rounded-md overflow-hidden shadow-sm ring-1 ring-zinc-700">
        @if($rating->movie->posterUrl())
            <img src="{{ $rating->movie->posterUrl() }}" alt="{{ $rating->movie->title }}"
                class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
        @else
            <div class="w-full h-full flex items-center justify-center p-2 text-center">
                <span class="text-xs text-zinc-500 leading-snug">{{ $rating->movie->title }}</span>
            </div>
        @endif
    </div>
    <div class="mt-1.5">
        <div class="text-sm font-medium text-zinc-200 truncate group-hover:text-amber-400 transition-colors">{{ $rating->movie->title }}</div>
        @if($rating->stars)
        <div class="text-xs">
            <x-star-display :value="$rating->stars" class="text-xs" />
        </div>
        @endif
        <div class="text-xs text-zinc-500 truncate">
            <a href="{{ route('profile.show', $rating->user->username) }}"
               class="hover:text-amber-400 hover:underline"
               onclick="event.stopPropagation()">{{ $rating->user->name }}</a>
        </div>
    </div>
</a>
