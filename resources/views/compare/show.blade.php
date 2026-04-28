<x-app-layout>

    {{-- Hero --}}
    <div class="bg-zinc-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            {{-- Back link --}}
            <div class="mb-6">
                <a href="{{ route('compare.index') }}" class="text-xs text-amber-400 hover:text-zinc-300 transition-colors">&larr; Compare another pair</a>
            </div>

            {{-- Director cards --}}
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-4 sm:gap-8">

                {{-- Director A --}}
                <div class="text-left">
                    <a href="{{ $personA->dominantTypeUrl() }}" class="group">
                        <div class="flex items-start gap-4">
                            @if($personA->photo)
                                <img src="{{ asset('storage/' . $personA->photo) }}" alt="{{ $personA->name }}"
                                     class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover ring-2 ring-white/20 shrink-0">
                            @else
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-zinc-800 flex items-center justify-center ring-2 ring-white/20 shrink-0">
                                    <span class="text-2xl font-bold text-amber-400">{{ strtoupper(substr($personA->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h2 class="text-xl sm:text-2xl font-bold leading-tight group-hover:text-zinc-200 transition-colors">{{ $personA->name }}</h2>
                                @if($personA->nationality)
                                    <p class="text-xs text-amber-400 mt-0.5">{{ $personA->nationality }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-3 text-sm">
                                    <span><span class="font-bold text-lg">{{ $dataA['films']->count() }}</span> <span class="text-zinc-400 text-xs">films</span></span>
                                    @if($dataA['overallAvg'])
                                        <span><span class="font-bold text-lg">{{ number_format($dataA['overallAvg'], 1) }}</span> <span class="text-zinc-400 text-xs">avg ★</span></span>
                                    @endif
                                    @if($dataA['firstYear'])
                                        <span class="text-zinc-400 text-xs self-end">{{ $dataA['firstYear'] }}–{{ $dataA['lastYear'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- VS --}}
                <div class="text-center">
                    <span class="text-2xl sm:text-3xl font-black text-amber-400 tracking-widest">VS</span>
                </div>

                {{-- Director B --}}
                <div class="text-right">
                    <a href="{{ $personB->dominantTypeUrl() }}" class="group">
                        <div class="flex items-start gap-4 flex-row-reverse">
                            @if($personB->photo)
                                <img src="{{ asset('storage/' . $personB->photo) }}" alt="{{ $personB->name }}"
                                     class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover ring-2 ring-white/20 shrink-0">
                            @else
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-zinc-800 flex items-center justify-center ring-2 ring-white/20 shrink-0">
                                    <span class="text-2xl font-bold text-amber-400">{{ strtoupper(substr($personB->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h2 class="text-xl sm:text-2xl font-bold leading-tight group-hover:text-zinc-200 transition-colors">{{ $personB->name }}</h2>
                                @if($personB->nationality)
                                    <p class="text-xs text-amber-400 mt-0.5">{{ $personB->nationality }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-3 text-sm justify-end">
                                    <span><span class="font-bold text-lg">{{ $dataB['films']->count() }}</span> <span class="text-zinc-400 text-xs">films</span></span>
                                    @if($dataB['overallAvg'])
                                        <span><span class="font-bold text-lg">{{ number_format($dataB['overallAvg'], 1) }}</span> <span class="text-zinc-400 text-xs">avg ★</span></span>
                                    @endif
                                    @if($dataB['firstYear'])
                                        <span class="text-zinc-400 text-xs self-end">{{ $dataB['firstYear'] }}–{{ $dataB['lastYear'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

        {{-- Stats comparison --}}
        @php
            $maxFilms = max($dataA['films']->count(), $dataB['films']->count(), 1);
            $maxAvg   = max($dataA['overallAvg'] ?? 0, $dataB['overallAvg'] ?? 0, 0.1);
        @endphp
        <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-800">
                <h2 class="text-sm font-semibold uppercase tracking-widest text-zinc-400">By the numbers</h2>
            </div>
            <div class="divide-y divide-zinc-800">

                {{-- Films --}}
                <div class="px-6 py-4 grid grid-cols-[1fr_auto_1fr] items-center gap-4">
                    <div class="flex items-center gap-2 justify-end">
                        <span class="text-sm font-semibold text-zinc-200">{{ $dataA['films']->count() }}</span>
                        <div class="h-2 rounded-full bg-amber-500 transition-all" style="width: {{ round(($dataA['films']->count() / $maxFilms) * 100) }}px; max-width: 160px"></div>
                    </div>
                    <span class="text-xs text-zinc-400 uppercase tracking-wide text-center w-20">Films</span>
                    <div class="flex items-center gap-2">
                        <div class="h-2 rounded-full bg-emerald-400 transition-all" style="width: {{ round(($dataB['films']->count() / $maxFilms) * 100) }}px; max-width: 160px"></div>
                        <span class="text-sm font-semibold text-zinc-200">{{ $dataB['films']->count() }}</span>
                    </div>
                </div>

                {{-- Avg rating --}}
                @if($dataA['overallAvg'] || $dataB['overallAvg'])
                <div class="px-6 py-4 grid grid-cols-[1fr_auto_1fr] items-center gap-4">
                    <div class="flex items-center gap-2 justify-end">
                        <span class="text-sm font-semibold text-zinc-200">{{ $dataA['overallAvg'] ? number_format($dataA['overallAvg'], 1) : '—' }}</span>
                        @if($dataA['overallAvg'])
                            <div class="h-2 rounded-full bg-amber-500" style="width: {{ round(($dataA['overallAvg'] / $maxAvg) * 100) }}px; max-width: 160px"></div>
                        @endif
                    </div>
                    <span class="text-xs text-zinc-400 uppercase tracking-wide text-center w-20">Avg ★</span>
                    <div class="flex items-center gap-2">
                        @if($dataB['overallAvg'])
                            <div class="h-2 rounded-full bg-emerald-400" style="width: {{ round(($dataB['overallAvg'] / $maxAvg) * 100) }}px; max-width: 160px"></div>
                        @endif
                        <span class="text-sm font-semibold text-zinc-200">{{ $dataB['overallAvg'] ? number_format($dataB['overallAvg'], 1) : '—' }}</span>
                    </div>
                </div>
                @endif

                {{-- Career span --}}
                <div class="px-6 py-4 grid grid-cols-[1fr_auto_1fr] items-center gap-4">
                    <div class="text-right">
                        @if($dataA['firstYear'])
                            <span class="text-sm font-semibold text-zinc-200">{{ $dataA['firstYear'] }}–{{ $dataA['lastYear'] }}</span>
                            <span class="text-xs text-zinc-400 ml-1">({{ $dataA['lastYear'] - $dataA['firstYear'] + 1 }}yr)</span>
                        @else
                            <span class="text-sm text-zinc-400">—</span>
                        @endif
                    </div>
                    <span class="text-xs text-zinc-400 uppercase tracking-wide text-center w-20">Career</span>
                    <div>
                        @if($dataB['firstYear'])
                            <span class="text-sm font-semibold text-zinc-200">{{ $dataB['firstYear'] }}–{{ $dataB['lastYear'] }}</span>
                            <span class="text-xs text-zinc-400 ml-1">({{ $dataB['lastYear'] - $dataB['firstYear'] + 1 }}yr)</span>
                        @else
                            <span class="text-sm text-zinc-400">—</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Decade activity --}}
        @if($dataA['decades']->isNotEmpty() || $dataB['decades']->isNotEmpty())
        @php
            $allDecades = $dataA['decades']->keys()->merge($dataB['decades']->keys())->unique()->sort()->values();
            $maxDecadeCount = max($dataA['decades']->max() ?? 0, $dataB['decades']->max() ?? 0, 1);
        @endphp
        <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-800">
                <h2 class="text-sm font-semibold uppercase tracking-widest text-zinc-400">Activity by decade</h2>
            </div>
            <div class="px-6 py-5">
                <div class="flex items-end gap-1 sm:gap-2">
                    @foreach($allDecades as $decade)
                    @php
                        $cA = $dataA['decades']->get($decade, 0);
                        $cB = $dataB['decades']->get($decade, 0);
                        $hA = $maxDecadeCount > 0 ? round(($cA / $maxDecadeCount) * 80) : 0;
                        $hB = $maxDecadeCount > 0 ? round(($cB / $maxDecadeCount) * 80) : 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full flex items-end justify-center gap-0.5" style="height: 80px">
                            <div class="w-2 sm:w-3 rounded-t bg-amber-500 transition-all" style="height: {{ $hA }}px" title="{{ $personA->name }}: {{ $cA }} film{{ $cA !== 1 ? 's' : '' }}"></div>
                            <div class="w-2 sm:w-3 rounded-t bg-emerald-400 transition-all" style="height: {{ $hB }}px" title="{{ $personB->name }}: {{ $cB }} film{{ $cB !== 1 ? 's' : '' }}"></div>
                        </div>
                        <span class="text-xs text-zinc-400">{{ $decade }}s</span>
                    </div>
                    @endforeach
                </div>
                {{-- Legend --}}
                <div class="flex items-center gap-6 mt-4 text-xs text-zinc-400">
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded bg-amber-500"></span>{{ $personA->name }}</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded bg-emerald-400"></span>{{ $personB->name }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Genre comparison --}}
        @if($dataA['topGenres']->isNotEmpty() || $dataB['topGenres']->isNotEmpty())
        <div class="grid sm:grid-cols-2 gap-6">

            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-800">
                    <h2 class="text-sm font-semibold uppercase tracking-widest text-zinc-400">{{ $personA->name }}'s top genres</h2>
                </div>
                <div class="px-6 py-4 space-y-2.5">
                    @forelse($dataA['topGenres'] as $genre)
                    @php $pct = $dataA['films']->count() > 0 ? round(($genre->movies_count / $dataA['films']->count()) * 100) : 0; @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-28 text-sm text-zinc-300 truncate">{{ $genre->name }}</span>
                        <div class="flex-1 h-2 bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-zinc-400 w-6 text-right">{{ $genre->movies_count }}</span>
                    </div>
                    @empty
                        <p class="text-sm text-zinc-400">No genre data.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-800">
                    <h2 class="text-sm font-semibold uppercase tracking-widest text-zinc-400">{{ $personB->name }}'s top genres</h2>
                </div>
                <div class="px-6 py-4 space-y-2.5">
                    @forelse($dataB['topGenres'] as $genre)
                    @php $pct = $dataB['films']->count() > 0 ? round(($genre->movies_count / $dataB['films']->count()) * 100) : 0; @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-28 text-sm text-zinc-300 truncate">{{ $genre->name }}</span>
                        <div class="flex-1 h-2 bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-zinc-400 w-6 text-right">{{ $genre->movies_count }}</span>
                    </div>
                    @empty
                        <p class="text-sm text-zinc-400">No genre data.</p>
                    @endforelse
                </div>
            </div>

        </div>
        @endif

        {{-- Shared cast --}}
        @if($sharedCast->isNotEmpty())
        <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-800">
                <h2 class="text-sm font-semibold uppercase tracking-widest text-zinc-400">Shared collaborators</h2>
                <p class="text-xs text-zinc-400 mt-0.5">People who appeared in films by both directors</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 divide-x divide-y divide-zinc-800">
                @foreach($sharedCast as $row)
                <a href="{{ route('people.show', $row['person']) }}"
                   class="px-4 py-3 hover:bg-zinc-800 transition-colors group">
                    <p class="text-sm font-medium text-zinc-200 group-hover:text-amber-400 transition-colors truncate">{{ $row['person']->name }}</p>
                    <div class="flex items-center gap-2 mt-1 text-xs text-zinc-400">
                        <span class="flex items-center gap-1">
                            <span class="inline-block w-2 h-2 rounded bg-amber-400"></span>
                            {{ $row['films_with_a'] }} film{{ $row['films_with_a'] !== 1 ? 's' : '' }}
                        </span>
                        <span class="text-zinc-400">·</span>
                        <span class="flex items-center gap-1">
                            <span class="inline-block w-2 h-2 rounded bg-emerald-400"></span>
                            {{ $row['films_with_b'] }} film{{ $row['films_with_b'] !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Filmographies side by side --}}
        <div class="grid sm:grid-cols-2 gap-8">

            {{-- Director A filmography --}}
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-widest text-zinc-400 mb-4">{{ $personA->name }}</h2>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($dataA['films'] as $credit)
                    <x-movie-poster-card
                        :movie="$credit->movie"
                        :avg-rating="$dataA['avgRatings']->get($credit->movie_id)" />
                    @endforeach
                </div>
            </div>

            {{-- Director B filmography --}}
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-widest text-zinc-400 mb-4">{{ $personB->name }}</h2>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($dataB['films'] as $credit)
                    <x-movie-poster-card
                        :movie="$credit->movie"
                        :avg-rating="$dataB['avgRatings']->get($credit->movie_id)" />
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
