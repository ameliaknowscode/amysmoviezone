@props(['movie', 'showRating' => false, 'avgRating' => null, 'subline' => null])

<a href="{{ $movie->publicUrl() }}" {{ $attributes->merge(['class' => 'group block']) }}>
    <div class="aspect-[2/3] bg-zinc-800 rounded-lg overflow-hidden shadow-sm ring-1 ring-zinc-700">
        @if($movie->posterUrl())
            <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                 class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
        @else
            <div class="w-full h-full flex items-center justify-center p-2 text-center">
                <span class="text-xs text-zinc-500 leading-snug">{{ $movie->title }}</span>
            </div>
        @endif
    </div>
    <div class="mt-1.5 space-y-0.5">
        <p class="text-xs font-medium text-zinc-200 group-hover:text-amber-400 transition-colors line-clamp-2 leading-snug">
            {{ $movie->title }}
        </p>
        @php $displayRating = $avgRating ?? ($showRating ? ($movie->avg_stars ?? null) : null); @endphp
        <div class="flex items-center gap-1.5">
            @if($movie->release_year)
                <span class="text-xs text-zinc-500">{{ $movie->release_year }}</span>
            @endif
            @if($displayRating)
                <span class="text-xs text-yellow-500 font-medium">★ {{ number_format($displayRating, 1) }}</span>
            @endif
        </div>
        @if($subline)
            <p class="text-xs text-zinc-500">{{ $subline }}</p>
        @endif
    </div>
</a>
