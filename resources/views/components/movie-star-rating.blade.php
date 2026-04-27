@props(['movie', 'userRating'])

<form method="POST" action="{{ route('movies.rate', $movie) }}" x-ref="ratingForm" class="flex items-center">
    @csrf
    <input type="hidden" name="stars" x-bind:value="stars">
    @foreach([1,2,3,4,5] as $star)
    <span
        class="relative inline-block text-2xl sm:text-3xl leading-none cursor-pointer select-none"
        @mousemove="hovered = $event.offsetX < $el.offsetWidth / 2 ? {{ $star - 0.5 }} : {{ $star }}"
        @mouseleave="hovered = 0"
        @click="stars = hovered; $nextTick(() => $refs.ratingForm.submit())"
        :title="(hovered || stars) + ' ' + ((hovered || stars) === 1 ? 'star' : 'stars')"
    >
        <span class="transition-colors"
              :class="(hovered || stars) >= {{ $star }} ? 'text-yellow-400' : 'text-zinc-600'"
        >&#9733;</span>
        <span
            class="absolute top-0 left-0 text-yellow-400 pointer-events-none transition-opacity"
            :class="(hovered || stars) >= {{ $star - 0.5 }} && (hovered || stars) < {{ $star }} ? 'opacity-100' : 'opacity-0'"
            style="clip-path: inset(0 50% 0 0)"
        >&#9733;</span>
    </span>
    @endforeach
</form>

@if($userRating?->stars)
<form method="POST" action="{{ route('movies.rating.destroy', $movie) }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-xs text-zinc-500 hover:text-zinc-300 transition underline">
        Remove
    </button>
</form>
@endif

<form method="POST" action="{{ route('movies.rate', $movie) }}">
    @csrf
    <input type="hidden" name="liked" value="{{ $userRating?->liked ? '0' : '1' }}">
    <button
        type="submit"
        class="focus:outline-none transition-colors {{ $userRating?->liked ? 'text-red-400' : 'text-amber-300 hover:text-red-400' }}"
        title="{{ $userRating?->liked ? 'Unlike' : 'Like' }}"
    >
        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
    </button>
</form>
