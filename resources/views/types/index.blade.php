<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            {{ __('Types') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 overflow-hidden sm:rounded-lg">
                <div class="p-6 text-zinc-100">

                    @if(session('success'))
                        <p class="mb-4 text-green-600">{{ session('success') }}</p>
                    @endif

                    <div class="mb-4">
                        <a href="{{ route('admin.types.create') }}" class="text-amber-400 hover:underline">+ Add Type</a>
                    </div>

                    @if($types->isEmpty())
                        <p>No types found.</p>
                    @else
                        <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-zinc-800 text-left">
                                    <th class="border border-zinc-700 px-4 py-2">ID</th>
                                    <th class="border border-zinc-700 px-4 py-2">Name</th>
                                    <th class="border border-zinc-700 px-4 py-2">Crew?</th>
                                    <th class="border border-zinc-700 px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $type)
                                    <tr class="hover:bg-zinc-800">
                                        <td class="border border-zinc-700 px-4 py-2">{{ $type->id }}</td>
                                        <td class="border border-zinc-700 px-4 py-2">{{ $type->name }}</td>
                                        <td class="border border-zinc-700 px-4 py-2">
                                            @if($type->is_crew)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-900/30 text-amber-300">Yes</span>
                                            @else
                                                <span class="text-zinc-500">—</span>
                                            @endif
                                        </td>
                                        <td class="border border-zinc-700 px-4 py-2 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <a href="{{ route('admin.types.edit', $type) }}" class="text-indigo-500 hover:text-amber-300" title="Edit type">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                    </svg>
                                                </a>
                                                <form method="POST" action="{{ route('admin.types.destroy', $type) }}"
                                                    onsubmit="return confirm('Are you sure you want to delete this type?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Delete type">
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
                        <div class="mt-4">{{ $types->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
