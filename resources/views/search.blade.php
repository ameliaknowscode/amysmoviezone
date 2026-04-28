<x-app-layout :title="'Search results' . (request('q') ? ' for ' . e(request('q')) : '')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            Results for &ldquo;{{ $query }}&rdquo;
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Movies --}}
            <div class="bg-zinc-900 sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-800 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-zinc-200">Movies</h3>
                    @if($movies->isNotEmpty())
                        <span class="text-xs font-medium bg-amber-900/20 text-amber-400 px-2 py-0.5 rounded-full">
                            {{ $movies->count() }}
                        </span>
                    @endif
                </div>

                @if($movies->isEmpty())
                    <p class="px-6 py-5 text-sm text-zinc-400">No movies found for &ldquo;{{ $query }}&rdquo;.</p>
                @else
                    <ul class="divide-y divide-zinc-800">
                        @foreach($movies as $movie)
                            <li class="flex items-start gap-4 px-6 py-4 hover:bg-zinc-800 transition-colors">
                                {{-- Poster --}}
                                <a href="{{ $movie->publicUrl() }}" class="shrink-0">
                                    <div class="w-10 h-[60px] rounded overflow-hidden bg-amber-900/20 shadow-sm ring-1 ring-black/5">
                                        @if($movie->posterUrl())
                                            <img src="{{ $movie->posterUrl() }}" alt="{{ $movie->title }}"
                                                 class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                </a>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <a href="{{ $movie->publicUrl() }}"
                                           class="text-sm font-semibold text-zinc-100 hover:text-amber-400 transition-colors truncate">
                                            {{ $movie->title }}
                                        </a>
                                        @if($movie->ratings_avg_stars)
                                            <span class="shrink-0 text-xs text-yellow-500 font-medium">
                                                ★ {{ number_format($movie->ratings_avg_stars, 1) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-zinc-400">
                                        @if($movie->release_year)
                                            <span>{{ $movie->release_year }}</span>
                                        @endif
                                        @if($movie->credits->isNotEmpty())
                                            <span>·</span>
                                            <span>
                                                @foreach($movie->credits as $i => $credit)
                                                    @if($i > 0), @endif
                                                    <a href="{{ $credit->byTypeUrl() }}"
                                                       class="text-amber-400 hover:text-amber-300 transition-colors">
                                                        {{ $credit->person->name }}
                                                    </a>
                                                @endforeach
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- People --}}
            <div class="bg-zinc-900 sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-800 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-zinc-200">People</h3>
                    @if($people->isNotEmpty())
                        <span class="text-xs font-medium bg-amber-900/20 text-amber-400 px-2 py-0.5 rounded-full">
                            {{ $people->count() }}
                        </span>
                    @endif
                </div>

                @if($people->isEmpty())
                    <p class="px-6 py-5 text-sm text-zinc-400">No people found for &ldquo;{{ $query }}&rdquo;.</p>
                @else
                    <ul class="divide-y divide-zinc-800">
                        @foreach($people as $person)
                            @php
                                $typeName  = $person->dominantTypeName();
                                $typeUrl   = $person->dominantTypeUrl();
                                $filmCount = $person->credits->count();
                            @endphp
                            <li class="flex items-center gap-4 px-6 py-4 hover:bg-zinc-800 transition-colors">
                                {{-- Avatar --}}
                                <div class="shrink-0 h-10 w-10 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 font-bold text-sm select-none">
                                    {{ strtoupper(substr($person->name, 0, 1)) }}
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ $typeUrl ?? route('people.show', $person) }}"
                                           class="text-sm font-semibold text-zinc-100 hover:text-amber-400 transition-colors">
                                            {{ $person->name }}
                                        </a>
                                        @if($typeName)
                                            <span class="text-xs font-medium bg-amber-900/20 text-amber-400 px-2 py-0.5 rounded-full">
                                                {{ $typeName }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-zinc-400">
                                        @if($person->nationality)
                                            <span>{{ $person->nationality }}</span>
                                        @endif
                                        @if($filmCount)
                                            @if($person->nationality)<span>·</span>@endif
                                            <span>{{ $filmCount }} {{ Str::plural('film', $filmCount) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
