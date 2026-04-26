@foreach($activities as $activity)
@php $item = $activity->item; $user = $item->user; $movie = $item->movie; @endphp
<div class="flex gap-4 p-4">

    {{-- Avatar --}}
    <a href="{{ route('profile.show', $user->username) }}" class="shrink-0">
        @if($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                 class="h-9 w-9 rounded-full object-cover ring-1 ring-gray-200">
        @else
            <div class="h-9 w-9 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 font-bold text-sm select-none">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        @endif
    </a>

    {{-- Poster --}}
    <a href="{{ $movie->publicUrl() }}" class="shrink-0 group">
        <div class="w-9 h-[54px] bg-zinc-700 rounded overflow-hidden shadow-sm">
            @if($movie->posterUrl())
                <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                     class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
            @endif
        </div>
    </a>

    {{-- Text --}}
    <div class="flex-1 min-w-0">
        <p class="text-sm text-zinc-200 leading-snug">
            <a href="{{ route('profile.show', $user->username) }}"
               class="font-medium hover:text-amber-400 transition-colors">{{ $user->name }}</a>

            @if($activity->type === 'rating')
                @if($item->stars)
                    rated
                    <a href="{{ $movie->publicUrl() }}" class="font-medium hover:text-amber-400 transition-colors">{{ $movie->title }}</a>
                    <x-star-display :value="$item->stars" class="text-xs" />
                @elseif($item->liked)
                    liked
                    <a href="{{ $movie->publicUrl() }}" class="font-medium hover:text-amber-400 transition-colors">{{ $movie->title }}</a>
                @else
                    logged
                    <a href="{{ $movie->publicUrl() }}" class="font-medium hover:text-amber-400 transition-colors">{{ $movie->title }}</a>
                @endif

            @elseif($activity->type === 'review')
                @if($item->body)
                    reviewed
                @else
                    logged
                @endif
                <a href="{{ $movie->publicUrl() }}" class="font-medium hover:text-amber-400 transition-colors">{{ $movie->title }}</a>
                @if($item->watched_at)
                    <span class="text-zinc-500">on {{ $item->watched_at->format('j M Y') }}</span>
                @endif

            @elseif($activity->type === 'watchlist')
                @if($item->list_type === 'want_to_watch')
                    wants to watch
                @else
                    marked
                @endif
                <a href="{{ $movie->publicUrl() }}" class="font-medium hover:text-amber-400 transition-colors">{{ $movie->title }}</a>
                @if($item->list_type === 'watched')
                    <span class="text-zinc-500">as watched</span>
                @endif
            @endif
        </p>

        @if($activity->type === 'review' && $item->body)
            <p class="text-sm text-zinc-500 mt-1 leading-relaxed line-clamp-2">{{ $item->body }}</p>
        @endif

        <p class="text-xs text-zinc-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
    </div>

</div>
@endforeach
