<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('profile.show', $profileUser->username) }}" class="hover:text-indigo-600 transition-colors">{{ $profileUser->name }}</a>'s Followers
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($followers->isEmpty())
                        <p class="text-sm text-gray-400">No followers yet.</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach($followers as $user)
                            <li class="py-4 flex items-center gap-4">
                                <a href="{{ route('profile.show', $user->username) }}" class="shrink-0">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}"
                                             alt="{{ $user->name }}"
                                             class="h-10 w-10 rounded-full object-cover ring-1 ring-gray-200">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm select-none">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('profile.show', $user->username) }}"
                                       class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                                        {{ $user->name }}
                                    </a>
                                    <p class="text-xs text-gray-500">&#64;{{ $user->username }}</p>
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
