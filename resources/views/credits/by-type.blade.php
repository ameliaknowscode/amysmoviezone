<x-app-layout>

    {{-- Hero Header --}}
    <div class="bg-gradient-to-br from-indigo-950 to-indigo-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <span class="inline-block text-xs font-semibold uppercase tracking-widest text-indigo-300 mb-2">
                        {{ $type->name }}
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-bold">{{ $person->name }}</h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-indigo-200">
                        @if($person->nationality)
                            <span>{{ $person->nationality }}</span>
                        @endif
                        @if($person->date_of_birth)
                            <span>b. {{ \Carbon\Carbon::parse($person->date_of_birth)->format('Y') }}</span>
                        @endif
                        @if($person->date_of_death)
                            <span>d. {{ \Carbon\Carbon::parse($person->date_of_death)->format('Y') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="flex gap-6 text-center sm:text-right shrink-0">
                    <div>
                        <p class="text-2xl font-bold">{{ $credits->count() }}</p>
                        <p class="text-xs text-indigo-300 uppercase tracking-wide">{{ Str::plural('Film', $credits->count()) }}</p>
                    </div>
                    @if($overallAvg)
                        <div>
                            <p class="text-2xl font-bold">{{ number_format($overallAvg, 1) }}</p>
                            <p class="text-xs text-indigo-300 uppercase tracking-wide">Avg Rating</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Type tabs --}}
        @if($personTypes->count() > 1)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex gap-1">
                    @foreach($personTypes as $t)
                        @php $isActive = $t->id === $type->id; @endphp
                        <a href="{{ route('credits.by-type', [\Illuminate\Support\Str::slug($t->name), $person->slug]) }}"
                           class="px-4 py-2.5 text-sm font-medium rounded-t-md transition-colors
                               {{ $isActive
                                   ? 'bg-white text-indigo-700'
                                   : 'text-indigo-200 hover:text-white hover:bg-white/10' }}">
                            {{ $t->name }}
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif
    </div>

    {{-- Filmography Grid --}}
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4 sm:gap-5">
                @foreach($credits as $credit)
                    <a href="{{ $credit->movie->publicUrl() }}" class="group block">
                        {{-- Poster --}}
                        <div class="aspect-[2/3] rounded-lg overflow-hidden bg-indigo-50 shadow-sm ring-1 ring-black/5">
                            @if($credit->movie->posterUrl())
                                <img src="{{ $credit->movie->posterUrl() }}"
                                     alt="{{ $credit->movie->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-indigo-100">
                                    <span class="text-indigo-300 text-3xl">🎬</span>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="mt-2 space-y-0.5">
                            <p class="text-xs font-medium text-gray-800 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">
                                {{ $credit->movie->title }}
                            </p>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-gray-400">{{ $credit->movie->release_year }}</span>
                                @if(isset($avgRatings[$credit->movie_id]))
                                    <span class="text-xs text-yellow-500 font-medium">
                                        ★ {{ number_format($avgRatings[$credit->movie_id]->avg_stars, 1) }}
                                    </span>
                                @endif
                            </div>
                            @if($credit->character)
                                <p class="text-xs text-gray-400 italic line-clamp-1">{{ $credit->character }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

</x-app-layout>
