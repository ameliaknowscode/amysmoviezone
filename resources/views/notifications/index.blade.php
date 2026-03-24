<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if($notifications->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-400 text-sm">
                    You're all caught up — no notifications yet.
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100">
                    @foreach($notifications as $notification)
                        @php $data = $notification->data; @endphp
                        <div class="flex items-start gap-4 px-5 py-4 {{ $notification->read_at ? '' : 'bg-indigo-50' }}">

                            {{-- Icon --}}
                            <div class="shrink-0 mt-0.5">
                                @if($data['type'] === 'user_followed')
                                    <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                @endif
                            </div>

                            {{-- Message --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 leading-snug">
                                    @if($data['type'] === 'user_followed')
                                        <a href="{{ route('profile.show', $data['follower_username']) }}"
                                           class="font-medium hover:text-indigo-600 transition-colors">{{ $data['follower_name'] }}</a>
                                        started following you.

                                    @elseif($data['type'] === 'review_liked')
                                        <a href="{{ route('profile.show', $data['liker_username']) }}"
                                           class="font-medium hover:text-indigo-600 transition-colors">{{ $data['liker_name'] }}</a>
                                        liked your review of
                                        <a href="{{ $data['movie_url'] }}"
                                           class="font-medium hover:text-indigo-600 transition-colors">{{ $data['movie_title'] }}</a>.

                                    @elseif($data['type'] === 'shared_log')
                                        <a href="{{ route('profile.show', $data['logger_username']) }}"
                                           class="font-medium hover:text-indigo-600 transition-colors">{{ $data['logger_name'] }}</a>
                                        also logged
                                        <a href="{{ $data['movie_url'] }}"
                                           class="font-medium hover:text-indigo-600 transition-colors">{{ $data['movie_title'] }}</a>.
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>

                            {{-- Dismiss --}}
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="shrink-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-gray-500 transition-colors p-1" title="Dismiss">
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
