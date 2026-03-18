<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Director Connections
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Search Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-sm text-gray-600 mb-4">Pick directors to see every actor who has appeared in at least one film by <em>each</em> of the selected directors.</p>

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
                        <div class="space-y-2 mb-4">
                            <template x-for="(val, idx) in directors" :key="idx">
                                <div class="flex items-center gap-2">
                                    <label class="text-sm font-medium text-gray-700 w-20 shrink-0" x-text="'Director ' + (idx + 1)"></label>
                                    <select
                                        :name="'directors[' + idx + ']'"
                                        x-model="directors[idx]"
                                        class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 min-w-48"
                                    >
                                        <option value="">— Select a director —</option>
                                        @foreach($directors as $director)
                                            <option
                                                value="{{ $director->id }}"
                                                x-show="!directors.some((v, j) => j !== idx && v === '{{ $director->id }}')"
                                            >{{ $director->name }}</option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="button"
                                        @click="remove(idx)"
                                        x-show="directors.length > 1"
                                        class="text-sm text-red-500 hover:text-red-700"
                                    >Remove</button>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center gap-4">
                            <button
                                type="button"
                                @click="add()"
                                x-show="directors.length < {{ $directors->count() }}"
                                class="text-sm text-indigo-600 hover:text-indigo-800"
                            >+ Add director</button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Results --}}
            @if($selectedDirectors->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">
                            Actors in films by {{ $selectedDirectors->pluck('name')->join(', ', ' & ') }}
                        </h3>

                        @if($actors->isEmpty())
                            <p class="text-gray-500">No actors found for the selected director(s).</p>
                        @else
                            <p class="text-sm text-gray-500 mb-3">{{ $actors->count() }} {{ Str::plural('actor', $actors->count()) }} found.</p>
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 text-left">
                                        <th class="border border-gray-300 px-4 py-2">Name</th>
                                        <th class="border border-gray-300 px-4 py-2">Nationality</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($actors as $actor)
                                        <tr class="hover:bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2">
                                                <a href="{{ $actor->dominantTypeUrl() ?? route('people.show', $actor) }}" class="text-indigo-600 hover:underline">{{ $actor->name }}</a>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2">{{ $actor->nationality ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endif

            <div>
                <a href="{{ route('home') }}" class="text-indigo-600 hover:underline">&larr; Back to home</a>
            </div>

        </div>
    </div>
</x-app-layout>
