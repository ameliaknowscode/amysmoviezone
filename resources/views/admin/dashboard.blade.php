<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Stats grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Users --}}
                <div class="card p-6 flex items-start gap-4">
                    <div class="flex-shrink-0 bg-amber-900/30 text-amber-400 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 00-3-3.87" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-zinc-100">{{ number_format($stats['total_users']) }}</p>
                        <p class="text-sm font-medium text-zinc-400 mt-0.5">Users</p>
                        @if ($stats['new_users_7_days'] > 0)
                            <p class="text-xs text-amber-400 mt-1">+{{ $stats['new_users_7_days'] }} this week</p>
                        @endif
                    </div>
                </div>

                {{-- Movies --}}
                <div class="card p-6 flex items-start gap-4">
                    <div class="flex-shrink-0 bg-pink-100 text-pink-600 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-zinc-100">{{ number_format($stats['total_movies']) }}</p>
                        <p class="text-sm font-medium text-zinc-400 mt-0.5">Movies</p>
                    </div>
                </div>

                {{-- Ratings --}}
                <div class="card p-6 flex items-start gap-4">
                    <div class="flex-shrink-0 bg-yellow-100 text-yellow-600 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-zinc-100">{{ number_format($stats['total_ratings']) }}</p>
                        <p class="text-sm font-medium text-zinc-400 mt-0.5">Ratings</p>
                        @if ($stats['average_stars'])
                            <p class="text-xs text-yellow-600 mt-1">avg {{ number_format($stats['average_stars'], 1) }} ★</p>
                        @endif
                    </div>
                </div>

                {{-- Likes --}}
                <div class="card p-6 flex items-start gap-4">
                    <div class="flex-shrink-0 bg-red-100 text-red-500 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-zinc-100">{{ number_format($stats['total_likes']) }}</p>
                        <p class="text-sm font-medium text-zinc-400 mt-0.5">Likes</p>
                    </div>
                </div>

                {{-- Watchlist entries --}}
                <div class="card p-6 flex items-start gap-4">
                    <div class="flex-shrink-0 bg-teal-100 text-teal-600 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-zinc-100">{{ number_format($stats['total_watchlist']) }}</p>
                        <p class="text-sm font-medium text-zinc-400 mt-0.5">Watchlist Entries</p>
                    </div>
                </div>

                {{-- Follows --}}
                <div class="card p-6 flex items-start gap-4">
                    <div class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-lg p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-zinc-100">{{ number_format($stats['total_follows']) }}</p>
                        <p class="text-sm font-medium text-zinc-400 mt-0.5">Follows</p>
                    </div>
                </div>

            </div>

            {{-- Recently joined users --}}
            <div class="card">
                <div class="px-6 py-4 border-b border-zinc-800">
                    <h3 class="text-base font-semibold text-zinc-200">Recently Joined</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ($recentUsers as $user)
                        <div class="px-6 py-4 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-zinc-100 truncate">{{ $user->name }}</span>
                                    <span class="text-zinc-400 text-sm truncate">{{ $user->username }}</span>
                                    @if ($user->is_admin)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-900/30 text-amber-300">Admin</span>
                                    @endif
                                </div>
                                <p class="text-sm text-zinc-400 truncate mt-0.5">{{ $user->email }}</p>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                @if ($user->email_verified_at)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-amber-500 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Unverified
                                    </span>
                                @endif
                                <p class="text-xs text-zinc-400 mt-0.5">{{ $user->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-3 border-t border-zinc-800 bg-zinc-900">
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-amber-400 hover:text-amber-300 font-medium">View all users →</a>
                </div>
            </div>

            {{-- Quick links --}}
            <div class="card p-6">
                <h3 class="text-base font-semibold text-zinc-200 mb-4">Manage</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-sm font-medium rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 00-3-3.87" />
                        </svg>
                        Users
                    </a>
                    <a href="{{ route('admin.movies.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-sm font-medium rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                        Movies
                    </a>
                    <a href="{{ route('admin.people.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-sm font-medium rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        People
                    </a>
                    <a href="{{ route('admin.types.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-sm font-medium rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10V5a2 2 0 012-2z" />
                        </svg>
                        Credit Types
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
