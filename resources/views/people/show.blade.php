<x-app-layout>

    {{-- Hero Header --}}
    <div class="bg-gradient-to-br from-indigo-950 to-indigo-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex gap-6 items-start">

                @if($person->photo)
                    <img src="{{ asset('storage/' . $person->photo) }}" alt="{{ $person->name }}"
                         class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover shrink-0 ring-2 ring-white/20 shadow-lg">
                @endif

                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl sm:text-4xl font-bold">{{ $person->name }}</h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-indigo-200">
                        @if($person->nationality)
                            <span>{{ $person->nationality }}</span>
                        @endif
                        @if($person->date_of_birth)
                            <span>b. {{ \Carbon\Carbon::parse($person->date_of_birth)->format('F j, Y') }}</span>
                        @endif
                        @if($person->date_of_death)
                            <span>d. {{ \Carbon\Carbon::parse($person->date_of_death)->format('F j, Y') }}</span>
                        @endif
                    </div>
                    @if($person->bio)
                        <p class="mt-3 text-sm text-indigo-200 leading-relaxed max-w-2xl">{{ $person->bio }}</p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Filmography --}}
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($person->credits->isEmpty())
                <p class="text-sm text-gray-400">No credits listed.</p>
            @else
                @php
                    $creditsByType = $person->credits->groupBy(fn($c) => $c->type->name);
                @endphp

                <div x-data="{ activeTab: '{{ $creditsByType->keys()->first() }}' }">

                    {{-- Type tabs --}}
                    <div class="flex gap-1 border-b border-gray-200 mb-8">
                        @foreach($creditsByType->keys() as $typeName)
                            <button @click="activeTab = '{{ $typeName }}'"
                                    :class="activeTab === '{{ $typeName }}'
                                        ? 'border-b-2 border-indigo-500 text-indigo-600'
                                        : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'"
                                    class="px-4 py-2.5 text-sm font-medium transition-colors -mb-px">
                                {{ $typeName }}
                                <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full"
                                      :class="activeTab === '{{ $typeName }}' ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-100 text-gray-400'">
                                    {{ $creditsByType[$typeName]->count() }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Grids per type --}}
                    @foreach($creditsByType as $typeName => $typeCredits)
                        <div x-show="activeTab === '{{ $typeName }}'" x-cloak>
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4 sm:gap-5">
                                @foreach($typeCredits as $credit)
                                    <a href="{{ $credit->movie->publicUrl() }}" class="group block">
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
                                        <div class="mt-2 space-y-0.5">
                                            <p class="text-xs font-medium text-gray-800 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">
                                                {{ $credit->movie->title }}
                                            </p>
                                            <span class="text-xs text-gray-400">{{ $credit->movie->release_year }}</span>
                                            @if($credit->character)
                                                <p class="text-xs text-gray-400 italic line-clamp-1">{{ $credit->character }}</p>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                </div>
            @endif
        </div>
    </div>

</x-app-layout>
