<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('profile.show', $profileUser->username) }}" class="hover:text-indigo-600 transition-colors">{{ $profileUser->name }}</a>'s Diary
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if($entries === null)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-500">This user's diary is private.</div>
                </div>

            @elseif($entries->isEmpty() && $paginator->total() === 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-400">No diary entries yet.</div>
                </div>

            @else
                <div class="space-y-8">
                    @foreach($entries as $monthKey => $monthEntries)
                    <div>
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y') }}
                        </h2>

                        <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100">
                            @foreach($monthEntries as $entry)
                            <div class="flex gap-4 p-4">

                                {{-- Day number --}}
                                <div class="w-8 shrink-0 text-center">
                                    <span class="text-lg font-bold text-gray-800 leading-none">{{ $entry->watched_at->format('j') }}</span>
                                    <span class="block text-xs text-gray-400">{{ $entry->watched_at->format('D') }}</span>
                                </div>

                                {{-- Poster --}}
                                <a href="{{ $entry->movie->publicUrl() }}" class="shrink-0 group">
                                    <div class="w-10 h-[60px] bg-gray-200 rounded overflow-hidden shadow-sm">
                                        @if($entry->movie->posterUrl())
                                            <img src="{{ $entry->movie->posterUrl() }}" alt="{{ $entry->movie->title }}"
                                                class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                        @endif
                                    </div>
                                </a>

                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                        <a href="{{ $entry->movie->publicUrl() }}"
                                           class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                                            {{ $entry->movie->title }}
                                        </a>
                                        @if($entry->movie->release_year)
                                            <span class="text-xs text-gray-400">{{ $entry->movie->release_year }}</span>
                                        @endif
                                        @if($entry->is_rewatch)
                                            <span class="text-xs text-indigo-500 border border-indigo-200 rounded px-1.5 py-0.5 leading-none">Rewatch</span>
                                        @endif
                                        @php $rating = $diaryRatings->get($entry->movie_id); @endphp
                                        @if($rating)
                                            <x-star-display :value="$rating->stars" class="text-xs" />
                                        @endif
                                    </div>
                                    @if($entry->body)
                                        <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $entry->body }}</p>
                                    @endif
                                </div>

                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($paginator->hasPages())
                    <div class="mt-6">
                        {{ $paginator->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
