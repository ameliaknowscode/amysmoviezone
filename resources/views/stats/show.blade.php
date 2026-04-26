<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            My Stats
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if($totalWatched === 0)
                <div class="card">
                    <div class="p-12 text-center">
                        <p class="text-zinc-500 mb-3">No stats yet — start marking movies as watched to see your breakdown here.</p>
                        <a href="{{ route('movies.browse') }}" class="text-sm font-semibold text-amber-400 hover:text-amber-300 underline underline-offset-2">Browse movies</a>
                    </div>
                </div>
            @else

            {{-- Overview --}}
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Overview</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-zinc-100">{{ number_format($totalWatched) }}</div>
                            <div class="text-xs text-zinc-500 uppercase tracking-wide mt-1">Films Watched</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-zinc-100">{{ number_format($totalRated) }}</div>
                            <div class="text-xs text-zinc-500 uppercase tracking-wide mt-1">Films Rated</div>
                        </div>
                        <div class="text-center">
                            @if($avgRating)
                                <div class="text-3xl font-bold text-zinc-100">{{ number_format($avgRating, 1) }}</div>
                                <div class="text-xs text-zinc-500 uppercase tracking-wide mt-1">Avg Rating</div>
                            @else
                                <div class="text-3xl font-bold text-zinc-600">&mdash;</div>
                                <div class="text-xs text-zinc-500 uppercase tracking-wide mt-1">Avg Rating</div>
                            @endif
                        </div>
                        <div class="text-center">
                            @if($totalMinutes > 0)
                                @php
                                    $hours = intdiv((int) $totalMinutes, 60);
                                    $days  = intdiv($hours, 24);
                                @endphp
                                @if($days >= 2)
                                    <div class="text-3xl font-bold text-zinc-100">{{ number_format($days) }}</div>
                                    <div class="text-xs text-zinc-500 uppercase tracking-wide mt-1">Days Watched</div>
                                @else
                                    <div class="text-3xl font-bold text-zinc-100">{{ number_format($hours) }}</div>
                                    <div class="text-xs text-zinc-500 uppercase tracking-wide mt-1">Hours Watched</div>
                                @endif
                            @else
                                <div class="text-3xl font-bold text-zinc-600">&mdash;</div>
                                <div class="text-xs text-zinc-500 uppercase tracking-wide mt-1">Time Watched</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Films by Year Watched --}}
            @if($byYearWatched->isNotEmpty())
            @php $maxYearCount = $byYearWatched->max('count'); @endphp
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Films Watched by Year</h3>
                    <div class="space-y-2.5">
                        @foreach($byYearWatched as $row)
                        @php $pct = $maxYearCount > 0 ? round(($row->count / $maxYearCount) * 100) : 0; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-12 text-sm text-zinc-500 text-right shrink-0">{{ $row->year }}</div>
                            <div class="flex-1 h-6 bg-zinc-800 rounded overflow-hidden">
                                <div class="h-full bg-amber-900/200 rounded" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="w-6 text-sm text-zinc-400 text-right shrink-0">{{ $row->count }}</div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-zinc-500 mt-4">Only includes films with a watch date recorded.</p>
                </div>
            </div>
            @endif

            {{-- Films by Release Decade --}}
            @if($byDecade->isNotEmpty())
            @php $maxDecadeCount = $byDecade->max('count'); @endphp
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Films by Release Decade</h3>
                    <div class="space-y-2.5">
                        @foreach($byDecade as $row)
                        @php $pct = $maxDecadeCount > 0 ? round(($row->count / $maxDecadeCount) * 100) : 0; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-14 text-sm text-zinc-500 text-right shrink-0">{{ $row->decade }}s</div>
                            <div class="flex-1 h-6 bg-zinc-800 rounded overflow-hidden">
                                <div class="h-full bg-violet-500 rounded" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="w-6 text-sm text-zinc-400 text-right shrink-0">{{ $row->count }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Top Genres --}}
            @if($byGenre->isNotEmpty())
            @php $maxGenreCount = $byGenre->max('count'); @endphp
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Top Genres</h3>
                    <div class="space-y-2.5">
                        @foreach($byGenre as $row)
                        @php $pct = $maxGenreCount > 0 ? round(($row->count / $maxGenreCount) * 100) : 0; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-28 text-sm text-zinc-500 text-right shrink-0 truncate" title="{{ $row->name }}">{{ $row->name }}</div>
                            <div class="flex-1 h-6 bg-zinc-800 rounded overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="w-6 text-sm text-zinc-400 text-right shrink-0">{{ $row->count }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Rating Distribution --}}
            @if($ratingDist->isNotEmpty())
            @php $maxRatingCount = $ratingDist->max('count'); @endphp
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Rating Distribution</h3>
                    <div class="space-y-2.5">
                        @for($stars = 5; $stars >= 1; $stars--)
                        @php
                            $row = $ratingDist->get($stars);
                            $count = $row?->count ?? 0;
                            $pct = $maxRatingCount > 0 ? round(($count / $maxRatingCount) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-24 flex justify-end shrink-0">
                                <x-star-display :value="$stars" class="text-sm" emptyClass="text-zinc-700" />
                            </div>
                            <div class="flex-1 h-6 bg-zinc-800 rounded overflow-hidden">
                                @if($count > 0)
                                    <div class="h-full bg-yellow-400 rounded" style="width: {{ $pct }}%"></div>
                                @endif
                            </div>
                            <div class="w-6 text-sm text-right shrink-0 {{ $count > 0 ? 'text-zinc-400' : 'text-zinc-600' }}">{{ $count }}</div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
            @endif

            {{-- Top Directors --}}
            @if($byDirector->isNotEmpty())
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Top Directors</h3>
                    <ol class="divide-y divide-zinc-800">
                        @foreach($byDirector as $i => $row)
                        <li class="py-2.5 flex items-center gap-4">
                            <span class="text-sm text-zinc-500 w-5 text-right shrink-0">{{ $i + 1 }}</span>
                            <a href="{{ route('people.show', $row->slug) }}"
                               class="flex-1 text-sm font-medium text-amber-400 hover:underline truncate">
                                {{ $row->name }}
                            </a>
                            <span class="text-sm text-zinc-500 shrink-0">{{ $row->count }} {{ Str::plural('film', $row->count) }}</span>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
            @endif

            @endif {{-- end totalWatched > 0 --}}

        </div>
    </div>
</x-app-layout>
