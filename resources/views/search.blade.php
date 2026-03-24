<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Results for &ldquo;{{ $query }}&rdquo;
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Movies --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">Movies</h3>
                    @if($movies->isNotEmpty())
                        <span class="text-xs font-medium bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">
                            {{ $movies->count() }}
                        </span>
                    @endif
                </div>

                @if($movies->isEmpty())
                    <p class="px-6 py-5 text-sm text-gray-400">No movies found for &ldquo;{{ $query }}&rdquo;.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($movies as $movie)
                            <li class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                                {{-- Poster --}}
                                <a href="{{ $movie->publicUrl() }}" class="shrink-0">
                                    <div class="w-10 h-[60px] rounded overflow-hidden bg-indigo-50 shadow-sm ring-1 ring-black/5">
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
                                           class="text-sm font-semibold text-gray-900 hover:text-indigo-600 transition-colors truncate">
                                            {{ $movie->title }}
                                        </a>
                                        @if($movie->ratings_avg_stars)
                                            <span class="shrink-0 text-xs text-yellow-500 font-medium">
                                                ★ {{ number_format($movie->ratings_avg_stars, 1) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                                        @if($movie->release_year)
                                            <span>{{ $movie->release_year }}</span>
                                        @endif
                                        @if($movie->credits->isNotEmpty())
                                            <span>·</span>
                                            <span>
                                                @foreach($movie->credits as $i => $credit)
                                                    @if($i > 0), @endif
                                                    <a href="{{ $credit->byTypeUrl() }}"
                                                       class="text-indigo-500 hover:text-indigo-700 transition-colors">
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
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">People</h3>
                    @if($people->isNotEmpty())
                        <span class="text-xs font-medium bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">
                            {{ $people->count() }}
                        </span>
                    @endif
                </div>

                @if($people->isEmpty())
                    <p class="px-6 py-5 text-sm text-gray-400">No people found for &ldquo;{{ $query }}&rdquo;.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($people as $person)
                            @php
                                $typeName  = $person->dominantTypeName();
                                $typeUrl   = $person->dominantTypeUrl();
                                $filmCount = $person->credits->count();
                            @endphp
                            <li class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                                {{-- Avatar --}}
                                <div class="shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm select-none">
                                    {{ strtoupper(substr($person->name, 0, 1)) }}
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ $typeUrl ?? route('people.show', $person) }}"
                                           class="text-sm font-semibold text-gray-900 hover:text-indigo-600 transition-colors">
                                            {{ $person->name }}
                                        </a>
                                        @if($typeName)
                                            <span class="text-xs font-medium bg-indigo-50 text-indigo-500 px-2 py-0.5 rounded-full">
                                                {{ $typeName }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
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
