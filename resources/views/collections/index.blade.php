<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Collections</h2>
            <a href="{{ route('admin.collections.create') }}"
               class="btn-amber inline-flex items-center px-4 py-2 text-sm">
                + Add Collection
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('success') }}</div>
            @endif

            <div class="card">
                <table class="min-w-full divide-y divide-zinc-700 text-sm">
                    <thead class="bg-zinc-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Films</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @forelse($collections as $collection)
                        <tr>
                            <td class="px-6 py-3 font-medium text-zinc-100">
                                <a href="{{ route('collections.public.show', $collection->slug) }}"
                                   class="hover:text-amber-400 transition-colors">{{ $collection->name }}</a>
                            </td>
                            <td class="px-6 py-3 text-zinc-500">{{ $collection->slug }}</td>
                            <td class="px-6 py-3 text-zinc-500">{{ $collection->movies_count }}</td>
                            <td class="px-6 py-3 text-right space-x-3">
                                <a href="{{ route('admin.collections.edit', $collection) }}"
                                   class="text-zinc-500 hover:text-amber-400 transition-colors" title="Edit">
                                    <svg class="inline w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.collections.destroy', $collection) }}"
                                      class="inline" onsubmit="return confirm('Delete this collection?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-zinc-500 hover:text-red-600 transition-colors" title="Delete">
                                        <svg class="inline w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-zinc-500">No collections yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($collections->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-800">{{ $collections->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
