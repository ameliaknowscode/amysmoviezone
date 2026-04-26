<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            Director Connections
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Search Form --}}
            <div class="bg-zinc-900 overflow-hidden sm:rounded-lg">
                <div class="p-6 text-zinc-100">
                    <p class="text-sm text-zinc-400 mb-5">
                        Pick two or more directors to discover every actor who has appeared in at least one film by <em>each</em> of them.
                        A great way to find unexpected connections.
                    </p>

                    @php
                        $initialDirectors = array_values(array_map('strval', request()->query('directors', [])));
                        while (count($initialDirectors) < 2) { $initialDirectors[] = ''; }
                    @endphp

                    <form
                        method="GET"
                        action="{{ route('director-connections') }}"
                        x-data="{
                            directors: {{ json_encode($initialDirectors) }},
                            add() { this.directors.push('') },
                            remove(i) { this.directors.splice(i, 1) }
                        }"
                    >
                        <div class="space-y-2 mb-5">
                            <template x-for="(val, idx) in directors" :key="idx">
                                <div class="flex items-center gap-2">
                                    <label class="text-sm font-medium text-zinc-500 w-20 shrink-0" x-text="'Director ' + (idx + 1)"></label>
                                    <select
                                        :name="'directors[' + idx + ']'"
                                        x-model="directors[idx]"
                                        class="border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 w-full sm:w-72"
                                    >
                                        <option value="">— Select a director —</option>
                                        @foreach($directors as $director)
                                            <option
                                                value="{{ $director->id }}"
                                                x-show="!directors.some((v, j) => j !== idx && v === {{ json_encode((string) $director->id) }})"
                                            >{{ $director->name }}</option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="button"
                                        @click="remove(idx)"
                                        x-show="directors.length > 1"
                                        class="text-zinc-500 hover:text-red-500 transition shrink-0"
                                        title="Remove"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center gap-4">
                            <button
                                type="button"
                                @click="add()"
                                x-show="directors.length < {{ $directors->count() }}"
                                class="text-sm text-amber-400 hover:text-amber-300 font-medium"
                            >+ Add director</button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-md hover:bg-amber-400 transition">
                                Find connections
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Results --}}
            @if($selectedDirectors->isNotEmpty())
                <div class="bg-zinc-900 overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-5 border-b border-zinc-800">
                        <h3 class="text-base font-semibold text-zinc-100">
                            Actors in films by {{ $selectedDirectors->pluck('name')->join(', ', ' & ') }}
                        </h3>
                        @if($actors->isNotEmpty())
                            <p class="mt-1 text-sm text-zinc-500">
                                {{ $actors->count() }} {{ Str::plural('actor', $actors->count()) }} appeared in at least one film by each director.
                            </p>
                        @endif
                    </div>

                    <div class="p-6">
                        @if($actors->isEmpty())
                            <div class="text-center py-8">
                                <p class="text-zinc-500 font-medium">No actors in common.</p>
                                <p class="text-sm text-zinc-500 mt-1">These directors haven't shared any cast members. Try a different combination.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-700">
                                    <thead>
                                        <tr class="text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                            <th class="pb-3 pr-6">Actor</th>
                                            @foreach($selectedDirectors as $dir)
                                                <th class="pb-3 pr-6">{{ $dir->name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-800">
                                        @foreach($actors as $actor)
                                            <tr class="hover:bg-zinc-800 transition">
                                                <td class="py-3 pr-6">
                                                    <a href="{{ $actor->dominantTypeUrl() ?? route('people.show', $actor) }}"
                                                       class="font-medium text-amber-400 hover:text-amber-300 hover:underline">
                                                        {{ $actor->name }}
                                                    </a>
                                                    @if($actor->nationality)
                                                        <span class="text-xs text-zinc-500 ml-1">{{ $actor->nationality }}</span>
                                                    @endif
                                                </td>
                                                @foreach($selectedDirectors as $dir)
                                                    <td class="py-3 pr-6 text-sm text-zinc-400">
                                                        @php $films = $filmsByActor[$actor->id][$dir->id] ?? []; @endphp
                                                        @if($films)
                                                            {{ implode(', ', array_unique($films)) }}
                                                        @else
                                                            <span class="text-zinc-600">—</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div>
                <a href="{{ route('home') }}" class="text-sm text-amber-400 hover:underline">&larr; Back to home</a>
            </div>

        </div>
    </div>
</x-app-layout>
