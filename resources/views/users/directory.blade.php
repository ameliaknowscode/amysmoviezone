<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Users
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-full">
                                User
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Watched">
                                {{-- Eye --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Want to Watch">
                                {{-- Bookmark --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Liked">
                                {{-- Heart --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </th>
                            @auth<th class="px-4 py-3"></th>@endauth
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- Avatar + Name --}}
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}"
                                             alt="{{ $user->name }}"
                                             class="h-9 w-9 rounded-full object-cover ring-1 ring-gray-200 shrink-0">
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm select-none shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        @if ($user->username)
                                            <a href="{{ route('profile.show', $user->username) }}"
                                               class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                                                {{ $user->name }}
                                            </a>
                                            <p class="text-xs text-gray-400">&#64;{{ $user->username }}</p>
                                        @else
                                            <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Watched --}}
                            <td class="px-6 py-3 text-center text-sm text-gray-700 whitespace-nowrap">
                                @if (!$user->watched_private)
                                    {{ number_format($user->watched_count) }}
                                @endif
                            </td>

                            {{-- Want to Watch --}}
                            <td class="px-6 py-3 text-center text-sm text-gray-700 whitespace-nowrap">
                                @if (!$user->want_to_watch_private)
                                    {{ number_format($user->want_to_watch_count) }}
                                @endif
                            </td>

                            {{-- Liked --}}
                            <td class="px-6 py-3 text-center text-sm text-gray-700 whitespace-nowrap">
                                {{ number_format($user->likes_count) }}
                            </td>

                            {{-- Follow --}}
                            @auth
                            <td class="px-4 py-3 text-center">
                                @if (Auth::id() !== $user->id && $user->username)
                                    @if ($followingIds->has($user->id))
                                        {{-- Already following: muted check --}}
                                        <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-100 text-gray-400" title="Following">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    @else
                                        {{-- Follow button --}}
                                        <form method="POST" action="{{ route('follow.store', $user->username) }}">
                                            @csrf
                                            <button type="submit"
                                                    title="Follow {{ $user->name }}"
                                                    class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                            @endauth

                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">{{ $users->links() }}</div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
