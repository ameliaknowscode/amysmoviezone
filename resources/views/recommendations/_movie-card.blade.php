<a href="{{ $movie->publicUrl() }}" class="group block">
    {{-- Poster --}}
    <div class="aspect-[2/3] rounded-lg overflow-hidden bg-zinc-700 shadow-sm ring-1 ring-black/5">
        @if($movie->posterUrl())
            <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
        @else
            <div class="w-full h-full flex items-center justify-center bg-zinc-800 p-2 text-center">
                <span class="text-xs text-zinc-500 leading-snug">{{ $movie->title }}</span>
            </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="mt-2 space-y-0.5">
        <p class="text-xs font-medium text-zinc-200 group-hover:text-amber-400 transition-colors line-clamp-2 leading-snug">
            {{ $movie->title }}
        </p>
        <div class="flex items-center gap-1.5">
            @if($movie->release_year)
                <span class="text-xs text-zinc-500">{{ $movie->release_year }}</span>
            @endif
            @if($movie->avg_stars)
                <span class="text-xs text-yellow-500 font-medium">★ {{ number_format($movie->avg_stars, 1) }}</span>
            @endif
        </div>
        @if($subline)
            <p class="text-xs text-zinc-500">{{ $subline }}</p>
        @endif
    </div>
</a>
