<a href="{{ $rating->movie->publicUrl() }}" class="group">
    <div class="aspect-[2/3] bg-gray-200 rounded-md overflow-hidden shadow-sm">
        @if($rating->movie->posterUrl())
            <img src="{{ $rating->movie->posterUrl() }}" alt="{{ $rating->movie->title }}"
                class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
        @else
            <div class="w-full h-full flex items-center justify-center p-2 text-center">
                <span class="text-xs text-gray-500 leading-snug">{{ $rating->movie->title }}</span>
            </div>
        @endif
    </div>
    <div class="mt-1.5">
        <div class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ $rating->movie->title }}</div>
        @if($rating->stars)
        <div class="text-xs">
            <x-star-display :value="$rating->stars" class="text-xs" />
        </div>
        @endif
        <div class="text-xs text-gray-500 truncate">
            <a href="{{ route('profile.show', $rating->user->username) }}"
               class="hover:text-indigo-600 hover:underline"
               onclick="event.stopPropagation()">{{ $rating->user->name }}</a>
        </div>
    </div>
</a>
