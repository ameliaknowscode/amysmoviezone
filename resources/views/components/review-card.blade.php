@props([
    'review',
    'rating'  => null,
    'isOwn'   => false,
    'liked'   => false,
])

<div x-data="{ editing: false, showComments: false }" class="px-5 py-4">
    <div class="flex gap-3">

        {{-- Avatar --}}
        <div class="shrink-0">
            @if($review->user->avatar)
                <img src="{{ asset('storage/' . $review->user->avatar) }}"
                     alt="{{ $review->user->name }}"
                     class="h-8 w-8 rounded-full object-cover ring-1 ring-zinc-700">
            @else
                <div class="h-8 w-8 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 font-bold text-sm select-none">
                    {{ strtoupper(substr($review->user->name, 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="flex-1 min-w-0">

            {{-- Read view --}}
            <div @if($isOwn) x-show="!editing" @endif>
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                    @if($isOwn)
                        <span class="text-sm font-medium text-zinc-100">{{ $review->user->name }}</span>
                    @else
                        <a href="{{ route('profile.show', $review->user->username) }}"
                           class="text-sm font-medium text-zinc-100 hover:text-amber-400 transition-colors">
                            {{ $review->user->name }}
                        </a>
                    @endif
                    @if($rating?->stars)
                        <x-star-display :value="$rating->stars" class="text-xs" />
                    @endif
                    @if($review->watched_at)
                        <span class="text-xs text-zinc-400">watched {{ $review->watched_at->format('j M Y') }}</span>
                    @endif
                    @if($review->is_rewatch)
                        <span class="text-xs text-zinc-400 border border-zinc-600 rounded px-1.5 py-0.5 leading-none">Rewatch</span>
                    @endif
                    @if($review->has_spoilers)
                        <span class="text-xs text-amber-600 border border-amber-200 rounded px-1.5 py-0.5 leading-none">Spoilers</span>
                    @endif
                    @if($isOwn)
                        <button @click="editing = true"
                                class="text-xs text-zinc-400 hover:text-amber-400 transition underline">
                            Edit
                        </button>
                    @else
                        <span class="text-xs text-zinc-400">{{ $review->created_at->diffForHumans() }}</span>
                    @endif
                </div>

                @if($review->body)
                    @if(!$isOwn && $review->has_spoilers)
                        <div x-data="{ revealed: false }" class="mt-1.5">
                            <button type="button"
                                    x-show="!revealed"
                                    @click="revealed = true"
                                    class="text-sm text-zinc-400 italic hover:text-zinc-200 transition-colors text-left">
                                <span aria-hidden="true">⚠</span>
                                Spoilers hidden — click to reveal
                            </button>
                            <p x-show="revealed" x-cloak class="text-sm text-zinc-300 leading-relaxed">{{ $review->body }}</p>
                        </div>
                    @else
                        <p class="text-sm text-zinc-300 mt-1.5 leading-relaxed">{{ $review->body }}</p>
                    @endif
                @endif

                {{-- Actions row --}}
                <div class="mt-2 flex items-center gap-3">
                    @if(!$isOwn)
                        @auth
                        <form method="POST"
                              action="{{ $liked
                                  ? route('reviews.likes.destroy', $review)
                                  : route('reviews.likes.store', $review) }}">
                            @csrf
                            @if($liked) @method('DELETE') @endif
                            <button type="submit"
                                    aria-label="{{ $liked ? 'Unlike this review' : 'Like this review' }}"
                                    aria-pressed="{{ $liked ? 'true' : 'false' }}"
                                    class="inline-flex items-center gap-1 text-xs transition-colors {{ $liked ? 'text-red-400 hover:text-red-500' : 'text-zinc-400 hover:text-red-400' }}">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                @if($review->likes_count > 0)
                                    {{ $review->likes_count }}
                                @endif
                            </button>
                        </form>
                        @endauth
                    @endif
                    <button @click="showComments = !showComments"
                            :aria-expanded="showComments ? 'true' : 'false'"
                            class="inline-flex items-center gap-1 text-xs text-zinc-400 hover:text-amber-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        {{ $review->comments->count() ?: '' }}
                        {{ $review->comments->count() === 1 ? 'comment' : ($review->comments->count() > 1 ? 'comments' : 'Comment') }}
                    </button>
                </div>
            </div>

            {{-- Edit form slot (own review only) --}}
            @if($isOwn)
                <div x-show="editing" x-cloak>
                    {{ $slot }}
                </div>
            @endif

            {{-- Comments thread --}}
            <div x-show="showComments" x-cloak class="mt-2 space-y-2">
                @foreach($review->comments as $comment)
                <div class="flex gap-2 text-sm">
                    <div class="shrink-0 h-6 w-6 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 text-xs font-bold select-none">
                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-zinc-200 text-xs">
                            <a href="{{ route('profile.show', $comment->user->username) }}" class="hover:text-amber-400 transition-colors">{{ $comment->user->username }}</a>
                        </span>
                        <span class="text-zinc-400 text-xs ml-1">{{ $comment->body }}</span>
                        <span class="text-zinc-400 text-xs ml-1">{{ $comment->created_at->diffForHumans() }}</span>
                        @auth
                            @if(auth()->id() === $comment->user_id || auth()->id() === $review->user_id)
                            <form method="POST" action="{{ route('review-comments.destroy', $comment) }}" class="inline ml-1">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        aria-label="Delete comment by {{ $comment->user->name }}"
                                        class="text-xs text-zinc-400 hover:text-red-400 transition-colors">
                                    <span aria-hidden="true">✕</span>
                                </button>
                            </form>
                            @endif
                        @endauth
                    </div>
                </div>
                @endforeach
                @auth
                <form method="POST" action="{{ route('reviews.comments.store', $review) }}" class="flex gap-2 mt-1">
                    @csrf
                    <label for="comment-input-{{ $review->id }}" class="sr-only">Add a comment to {{ $review->user->name }}'s review</label>
                    <input id="comment-input-{{ $review->id }}" type="text" name="body" placeholder="Add a comment…" maxlength="1000"
                           class="flex-1 rounded-md border-zinc-800 bg-zinc-800 text-zinc-100 placeholder-zinc-500 shadow-sm text-xs focus:border-amber-500 focus:ring-amber-500 py-1.5">
                    <button type="submit" class="btn-amber px-3 py-1.5 text-xs">Post</button>
                </form>
                @endauth
            </div>

        </div>
    </div>
</div>
