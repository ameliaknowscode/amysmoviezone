<x-app-layout title="Notifications">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Notifications</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if($notifications->isEmpty())
                <div class="bg-zinc-900 sm:rounded-lg p-10 text-center text-zinc-400 text-sm">
                    You're all caught up — no notifications yet.
                </div>
            @else
                <div class="bg-zinc-900 sm:rounded-lg divide-y divide-zinc-800">
                    @foreach($notifications as $notification)
                        @php $data = $notification->data; @endphp
                        <div class="flex items-start gap-4 px-5 py-4 {{ $notification->read_at ? '' : 'bg-amber-900/20' }}">

                            {{-- Icon --}}
                            <div class="shrink-0 mt-0.5">
                                @if($data['type'] === 'user_followed')
                                    <div class="h-9 w-9 rounded-full bg-amber-900/30 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @elseif($data['type'] === 'review_liked')
                                    <div class="h-9 w-9 rounded-full bg-red-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-red-400" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </div>
                                @elseif($data['type'] === 'shared_log')
                                    <div class="h-9 w-9 rounded-full bg-emerald-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </div>
                                @elseif($data['type'] === 'list_item_added')
                                    <div class="h-9 w-9 rounded-full bg-violet-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </div>
                                @elseif($data['type'] === 'review_commented')
                                    <div class="h-9 w-9 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                    </div>
                                @elseif($data['type'] === 'shared_log' && ($data['same_night'] ?? false))
                                    <div class="h-9 w-9 rounded-full bg-amber-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Message --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-zinc-200 leading-snug">
                                    @if($data['type'] === 'user_followed')
                                        <a href="{{ route('profile.show', $data['follower_username']) }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['follower_name'] }}</a>
                                        started following you.

                                    @elseif($data['type'] === 'review_liked')
                                        <a href="{{ route('profile.show', $data['liker_username']) }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['liker_name'] }}</a>
                                        liked your review of
                                        <a href="{{ $data['movie_url'] }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['movie_title'] }}</a>.

                                    @elseif($data['type'] === 'shared_log')
                                        <a href="{{ route('profile.show', $data['logger_username']) }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['logger_name'] }}</a>
                                        @if($data['same_night'] ?? false)
                                            watched
                                            <a href="{{ $data['movie_url'] }}"
                                               class="font-medium hover:text-amber-400 transition-colors">{{ $data['movie_title'] }}</a>
                                            on the same night as you!
                                        @else
                                            also logged
                                            <a href="{{ $data['movie_url'] }}"
                                               class="font-medium hover:text-amber-400 transition-colors">{{ $data['movie_title'] }}</a>.
                                        @endif

                                    @elseif($data['type'] === 'review_commented')
                                        <a href="{{ route('profile.show', $data['commenter_username']) }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['commenter_name'] }}</a>
                                        commented on your review of
                                        <a href="{{ $data['movie_url'] }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['movie_title'] }}</a>.

                                    @elseif($data['type'] === 'list_item_added')
                                        <a href="{{ route('profile.show', $data['owner_username']) }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['owner_name'] }}</a>
                                        added
                                        <a href="{{ $data['movie_url'] }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['movie_title'] }}</a>
                                        to
                                        <a href="{{ $data['list_url'] }}"
                                           class="font-medium hover:text-amber-400 transition-colors">{{ $data['list_name'] }}</a>.
                                    @endif
                                </p>
                                <p class="text-xs text-zinc-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>

                            {{-- Dismiss --}}
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="shrink-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-zinc-400 hover:text-zinc-200 transition-colors p-1" title="Dismiss" aria-label="Dismiss notification">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
