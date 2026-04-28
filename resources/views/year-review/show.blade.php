<x-app-layout :title="$year . ' in Review'">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            Year in Review
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Year navigation hero --}}
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-10 flex items-center justify-between">
                    <div>
                        @php
                            $prevYear = $availableYears->filter(fn($y) => $y < $year)->last();
                            $nextYear = $availableYears->filter(fn($y) => $y > $year)->first();
                        @endphp
                        @if($prevYear)
                            <a href="{{ route('year-review.show', $prevYear) }}"
                               class="inline-flex items-center gap-1 text-indigo-200 hover:text-white text-sm font-medium transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                {{ $prevYear }}
                            </a>
                        @else
                            <span class="w-16 inline-block"></span>
                        @endif
                    </div>

                    <div class="text-center">
                        <div class="text-7xl font-black text-white tracking-tight">{{ $year }}</div>
                        <div class="text-indigo-200 text-sm font-medium uppercase tracking-widest mt-1">Year in Review</div>
                    </div>

                    <div>
                        @if($nextYear)
                            <a href="{{ route('year-review.show', $nextYear) }}"
                               class="inline-flex items-center gap-1 text-indigo-200 hover:text-white text-sm font-medium transition">
                                {{ $nextYear }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @else
                            <span class="w-16 inline-block"></span>
                        @endif
                    </div>
                </div>

                {{-- Year picker --}}
                @if($availableYears->count() > 1)
                <div class="border-t border-amber-500/40 px-6 py-3 flex items-center gap-3 flex-wrap">
                    <span class="text-indigo-300 text-xs uppercase tracking-widest">Jump to</span>
                    @foreach($availableYears as $y)
                        @if($y == $year)
                            <span class="text-white text-xs font-bold bg-zinc-900/20 rounded px-2 py-0.5">{{ $y }}</span>
                        @else
                            <a href="{{ route('year-review.show', $y) }}"
                               class="text-indigo-200 hover:text-white text-xs font-medium transition">{{ $y }}</a>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>

            @if($totalWatched === 0)
                <div class="card">
                    <div class="p-12 text-center">
                        <p class="text-zinc-400 mb-3">No films logged for {{ $year }} — add watch dates to your diary to see your year in review.</p>
                        <a href="{{ route('profile.diary', auth()->user()->username) }}" class="text-sm font-semibold text-amber-400 hover:text-amber-300 underline underline-offset-2">Go to diary</a>
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
                            <div class="text-xs text-zinc-400 uppercase tracking-wide mt-1">Films Watched</div>
                        </div>
                        <div class="text-center">
                            @if($totalMinutes > 0)
                                @php
                                    $hours = intdiv((int) $totalMinutes, 60);
                                    $days  = intdiv($hours, 24);
                                @endphp
                                @if($days >= 2)
                                    <div class="text-3xl font-bold text-zinc-100">{{ number_format($days) }}</div>
                                    <div class="text-xs text-zinc-400 uppercase tracking-wide mt-1">Days Watched</div>
                                @else
                                    <div class="text-3xl font-bold text-zinc-100">{{ number_format($hours) }}</div>
                                    <div class="text-xs text-zinc-400 uppercase tracking-wide mt-1">Hours Watched</div>
                                @endif
                            @else
                                <div class="text-3xl font-bold text-zinc-400">&mdash;</div>
                                <div class="text-xs text-zinc-400 uppercase tracking-wide mt-1">Time Watched</div>
                            @endif
                        </div>
                        <div class="text-center">
                            @if($avgRating)
                                <div class="text-3xl font-bold text-zinc-100">{{ number_format($avgRating, 1) }}</div>
                                <div class="text-xs text-zinc-400 uppercase tracking-wide mt-1">Avg Rating</div>
                            @else
                                <div class="text-3xl font-bold text-zinc-400">&mdash;</div>
                                <div class="text-xs text-zinc-400 uppercase tracking-wide mt-1">Avg Rating</div>
                            @endif
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-zinc-100">{{ number_format($totalRewatches) }}</div>
                            <div class="text-xs text-zinc-400 uppercase tracking-wide mt-1">Rewatches</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Highest-rated film --}}
            @if($highestRated)
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Favourite Film</h3>
                    <a href="{{ route('movies.public', $highestRated->slug) }}"
                       class="flex items-center gap-5 group">
                        @if($highestRated->poster)
                            <img src="{{ asset('storage/' . $highestRated->poster) }}"
                                 alt="{{ $highestRated->title }}"
                                 class="w-16 h-24 object-cover rounded shadow-sm shrink-0 group-hover:shadow-md transition">
                        @else
                            <div class="w-16 h-24 bg-zinc-800 rounded flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <div class="text-base font-semibold text-zinc-100 group-hover:text-amber-400 transition">
                                {{ $highestRated->title }}
                            </div>
                            <div class="text-sm text-zinc-400 mt-0.5">{{ $highestRated->release_year }}</div>
                            <div class="mt-2">
                                <x-star-display :value="$highestRated->stars" />
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endif

            {{-- Month by month --}}
            @php $maxMonthCount = $byMonth->max('count'); @endphp
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Month by Month</h3>
                    <div class="space-y-2.5">
                        @foreach($byMonth as $row)
                        @php $pct = $maxMonthCount > 0 ? round(($row->count / $maxMonthCount) * 100) : 0; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-8 text-xs text-zinc-400 text-right shrink-0">
                                {{ \Carbon\Carbon::create()->month($row->month)->format('M') }}
                            </div>
                            <div class="flex-1 h-6 bg-zinc-800 rounded overflow-hidden">
                                @if($row->count > 0)
                                    <div class="h-full bg-amber-500 rounded transition-all" style="width: {{ $pct }}%"></div>
                                @endif
                            </div>
                            <div class="w-6 text-sm text-right shrink-0 {{ $row->count > 0 ? 'text-zinc-400' : 'text-zinc-400' }}">
                                {{ $row->count > 0 ? $row->count : '' }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

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
                            <div class="w-28 text-sm text-zinc-400 text-right shrink-0 truncate" title="{{ $row->name }}">{{ $row->name }}</div>
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

            {{-- Top Directors --}}
            @if($byDirector->isNotEmpty())
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-zinc-200 mb-5">Top Directors</h3>
                    <ol class="divide-y divide-zinc-800">
                        @foreach($byDirector as $i => $row)
                        <li class="py-2.5 flex items-center gap-4">
                            <span class="text-sm text-zinc-400 w-5 text-right shrink-0">{{ $i + 1 }}</span>
                            <a href="{{ route('people.show', $row->slug) }}"
                               class="flex-1 text-sm font-medium text-amber-400 hover:underline truncate">
                                {{ $row->name }}
                            </a>
                            <span class="text-sm text-zinc-400 shrink-0">{{ $row->count }} {{ Str::plural('film', $row->count) }}</span>
                        </li>
                        @endforeach
                    </ol>
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
                        @for($stars = 5; $stars >= 0.5; $stars -= 0.5)
                        @php
                            $row   = $ratingDist->get($stars);
                            $count = $row?->count ?? 0;
                            $pct   = $maxRatingCount > 0 ? round(($count / $maxRatingCount) * 100) : 0;
                        @endphp
                        @if($count > 0 || $stars == floor($stars))
                        <div class="flex items-center gap-3">
                            <div class="w-24 flex justify-end shrink-0">
                                <x-star-display :value="$stars" class="text-sm" emptyClass="text-zinc-700" />
                            </div>
                            <div class="flex-1 h-6 bg-zinc-800 rounded overflow-hidden">
                                @if($count > 0)
                                    <div class="h-full bg-yellow-400 rounded" style="width: {{ $pct }}%"></div>
                                @endif
                            </div>
                            <div class="w-6 text-sm text-right shrink-0 {{ $count > 0 ? 'text-zinc-400' : 'text-zinc-400' }}">{{ $count ?: '' }}</div>
                        </div>
                        @endif
                        @endfor
                    </div>
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
                            <div class="w-14 text-sm text-zinc-400 text-right shrink-0">{{ $row->decade }}s</div>
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

            @endif {{-- end totalWatched > 0 --}}

        </div>
    </div>
</x-app-layout>
