@props(['movie', 'userRating'])

@php $currentStars = $userRating?->stars; @endphp
<form method="POST" action="{{ route('movies.rate', $movie) }}" x-ref="ratingForm" class="flex items-center">
    @csrf
    <fieldset class="flex items-center" role="radiogroup" aria-label="Rate {{ $movie->title }}">
        <legend class="sr-only">Rate {{ $movie->title }}</legend>
        @foreach([1,2,3,4,5] as $star)
        <span class="relative inline-block text-2xl sm:text-3xl leading-none select-none">
            {{-- Half-star clickable area (left half) --}}
            <label @mouseover="hovered = {{ $star - 0.5 }}"
                   @mouseleave="hovered = 0"
                   class="absolute top-0 left-0 w-1/2 h-full cursor-pointer z-10 focus-within:outline focus-within:outline-2 focus-within:outline-amber-400 focus-within:rounded-sm">
                <input type="radio" name="stars" value="{{ $star - 0.5 }}"
                       class="sr-only"
                       @change="stars = {{ $star - 0.5 }}; $refs.ratingForm.submit()"
                       @if($currentStars !== null && (float) $currentStars === (float) ($star - 0.5)) checked @endif>
                <span class="sr-only">{{ rtrim(rtrim(number_format($star - 0.5, 1), '0'), '.') }} {{ ($star - 0.5) === 1.0 ? 'star' : 'stars' }}</span>
            </label>
            {{-- Full-star clickable area (right half) --}}
            <label @mouseover="hovered = {{ $star }}"
                   @mouseleave="hovered = 0"
                   class="absolute top-0 right-0 w-1/2 h-full cursor-pointer z-10 focus-within:outline focus-within:outline-2 focus-within:outline-amber-400 focus-within:rounded-sm">
                <input type="radio" name="stars" value="{{ $star }}"
                       class="sr-only"
                       @change="stars = {{ $star }}; $refs.ratingForm.submit()"
                       @if($currentStars !== null && (float) $currentStars === (float) $star) checked @endif>
                <span class="sr-only">{{ $star }} {{ $star === 1 ? 'star' : 'stars' }}</span>
            </label>
            {{-- Visual full star --}}
            <span aria-hidden="true" class="block transition-colors"
                  :class="(hovered || stars) >= {{ $star }} ? 'text-yellow-400' : 'text-zinc-400'">&#9733;</span>
            {{-- Visual half-star overlay --}}
            <span aria-hidden="true"
                  class="absolute top-0 left-0 text-yellow-400 transition-opacity"
                  :class="(hovered || stars) >= {{ $star - 0.5 }} && (hovered || stars) < {{ $star }} ? 'opacity-100' : 'opacity-0'"
                  style="clip-path: inset(0 50% 0 0); pointer-events: none;">&#9733;</span>
        </span>
        @endforeach
    </fieldset>
</form>

@if($userRating?->stars)
<form method="POST" action="{{ route('movies.rating.destroy', $movie) }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-xs text-zinc-400 hover:text-zinc-300 transition underline">
        Remove
    </button>
</form>
@endif

<form method="POST" action="{{ route('movies.rate', $movie) }}">
    @csrf
    <input type="hidden" name="liked" value="{{ $userRating?->liked ? '0' : '1' }}">
    <button
        type="submit"
        aria-label="{{ $userRating?->liked ? 'Unlike this movie' : 'Like this movie' }}"
        aria-pressed="{{ $userRating?->liked ? 'true' : 'false' }}"
        class="transition-colors {{ $userRating?->liked ? 'text-red-400' : 'text-amber-300 hover:text-red-400' }}"
    >
        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
    </button>
</form>
