<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $movie->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <dl class="divide-y divide-gray-100">
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Title</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $movie->title }}</dd>
                        </div>
                        @if(isset($crew['Director']))
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">{{ $crew['Director']->count() === 1 ? 'Director' : 'Directors' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                @foreach($crew['Director'] as $i => $c)
                                    @if($i > 0), @endif
                                    <a href="{{ $c->byTypeUrl() }}" class="text-indigo-600 hover:underline">{{ $c->person->name }}</a>
                                @endforeach
                            </dd>
                        </div>
                        @endif
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Release Year</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $movie->release_year }}</dd>
                        </div>
                    </dl>

                    @if($cast->isNotEmpty() || $crew->isNotEmpty())
                    <div x-data="{ tab: 'cast' }" class="mt-6">

                        {{-- Tab bar --}}
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex gap-6">
                                <button
                                    type="button"
                                    @click="tab = 'cast'"
                                    :class="tab === 'cast'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap border-b-2 py-3 text-sm font-medium transition-colors">
                                    Cast
                                </button>
                                <button
                                    type="button"
                                    @click="tab = 'crew'"
                                    :class="tab === 'crew'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap border-b-2 py-3 text-sm font-medium transition-colors">
                                    Crew
                                </button>
                            </nav>
                        </div>

                        {{-- Cast panel --}}
                        <div x-show="tab === 'cast'" class="pt-4">
                            @if($cast->isNotEmpty())
                                <ul class="space-y-1 text-sm">
                                    @foreach($cast as $credit)
                                        <li>
                                            <a href="{{ $credit->byTypeUrl() }}" class="text-indigo-600 hover:underline">{{ $credit->person->name }}</a>
                                            @if($credit->character)
                                                <span class="text-gray-500">as {{ $credit->character }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-400">No cast listed.</p>
                            @endif
                        </div>

                        {{-- Crew panel --}}
                        <div x-show="tab === 'crew'" class="pt-4">
                            @if($crew->isNotEmpty())
                                <dl class="space-y-4">
                                    @foreach($crew as $typeName => $credits)
                                        <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500">{{ $typeName }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                <ul class="space-y-1">
                                                    @foreach($credits as $credit)
                                                        <li>
                                                            <a href="{{ $credit->byTypeUrl() }}" class="text-indigo-600 hover:underline">{{ $credit->person->name }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="text-sm text-gray-400">No crew listed.</p>
                            @endif
                        </div>

                    </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ url()->previous() }}" class="text-indigo-600 hover:underline text-sm">&larr; Back</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
