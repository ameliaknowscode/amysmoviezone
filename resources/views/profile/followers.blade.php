<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            <a href="{{ route('profile.show', $profileUser->username) }}" class="hover:text-amber-400 transition-colors">{{ $profileUser->name }}</a>'s Followers
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-6">
                    @if($followers->isEmpty())
                        <p class="text-sm text-zinc-400">No followers yet.</p>
                    @else
                        <ul class="divide-y divide-zinc-800">
                            @foreach($followers as $user)
                            <li class="py-4 flex items-center gap-4">
                                <a href="{{ route('profile.show', $user->username) }}" class="shrink-0">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}"
                                             alt="{{ $user->name }}"
                                             class="h-10 w-10 rounded-full object-cover ring-1 ring-gray-200">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 font-bold text-sm select-none">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('profile.show', $user->username) }}"
                                       class="text-sm font-medium text-zinc-100 hover:text-amber-400 transition-colors">
                                        {{ $user->name }}
                                    </a>
                                    <p class="text-xs text-zinc-400">&#64;{{ $user->username }}</p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
