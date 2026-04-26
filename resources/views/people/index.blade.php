<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            {{ __('People') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-6 text-zinc-100">

                    @if(session('success'))
                        <p class="mb-4 text-green-600">{{ session('success') }}</p>
                    @endif

                    @php
                        $sortLink = fn(string $col) => route('admin.people.index', [
                            'search'    => $search,
                            'sort_by'   => $col,
                            'direction' => ($sortBy === $col && $direction === 'asc') ? 'desc' : 'asc',
                        ]);
                        $sortIcon = fn(string $col) => $sortBy === $col
                            ? ($direction === 'asc' ? ' ↑' : ' ↓')
                            : '';
                    @endphp

                    <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
                        <form method="GET" action="{{ route('admin.people.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                            <input type="hidden" name="sort_by"   value="{{ $sortBy }}">
                            <input type="hidden" name="direction" value="{{ $direction }}">
                            <input type="text" name="search" value="{{ $search }}"
                                placeholder="Filter by name or nationality…"
                                class="border-zinc-700 rounded-md shadow-sm text-sm w-full sm:w-72">
                            <button type="submit"
                                class="btn-amber px-3 py-1.5 text-sm">
                                Filter
                            </button>
                            @if($search)
                                <a href="{{ route('admin.people.index', ['sort_by' => $sortBy, 'direction' => $direction]) }}"
                                   class="text-sm text-zinc-500 hover:underline">Clear</a>
                            @endif
                        </form>
                        <a href="{{ route('admin.people.create') }}" class="text-amber-400 hover:underline text-sm shrink-0">+ Add Person</a>
                    </div>

                    @if($people->isEmpty())
                        <p>No people found.</p>
                    @else
                        <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-zinc-800 text-left">
                                    <th class="border border-zinc-700 px-4 py-2">ID</th>
                                    <th class="border border-zinc-700 px-4 py-2">
                                        <a href="{{ $sortLink('name') }}" class="hover:underline">Name{{ $sortIcon('name') }}</a>
                                    </th>
                                    <th class="border border-zinc-700 px-4 py-2">
                                        <a href="{{ $sortLink('date_of_birth') }}" class="hover:underline">Date of Birth{{ $sortIcon('date_of_birth') }}</a>
                                    </th>
                                    <th class="border border-zinc-700 px-4 py-2">
                                        <a href="{{ $sortLink('nationality') }}" class="hover:underline">Nationality{{ $sortIcon('nationality') }}</a>
                                    </th>
                                    <th class="border border-zinc-700 px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($people as $person)
                                    <tr class="hover:bg-zinc-800">
                                        <td class="border border-zinc-700 px-4 py-2">{{ $person->id }}</td>
                                        <td class="border border-zinc-700 px-4 py-2">{{ $person->name }}</td>
                                        <td class="border border-zinc-700 px-4 py-2">
                                            {{ $person->date_of_birth ? \Carbon\Carbon::parse($person->date_of_birth)->format('F j, Y') : '—' }}
                                        </td>
                                        <td class="border border-zinc-700 px-4 py-2">{{ $person->nationality ?? '—' }}</td>
                                        <td class="border border-zinc-700 px-4 py-2 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <a href="{{ route('admin.people.edit', $person) }}" class="text-indigo-500 hover:text-amber-300" title="Edit person">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                    </svg>
                                                </a>
                                                <form method="POST" action="{{ route('admin.people.destroy', $person) }}"
                                                    onsubmit="return confirm('Are you sure you want to delete this person?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Delete person">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="3 6 5 6 21 6"/>
                                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                            <path d="M10 11v6"/>
                                                            <path d="M14 11v6"/>
                                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        <div class="mt-4">{{ $people->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
