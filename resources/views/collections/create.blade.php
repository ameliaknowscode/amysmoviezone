<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Add Collection</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card p-6">

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-md text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.collections.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-zinc-300 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full max-w-md rounded-md border-zinc-700 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-300 mb-1">Description <span class="text-zinc-500 font-normal">(optional)</span></label>
                        <textarea name="description" rows="3"
                                  class="w-full rounded-md border-zinc-700 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit"
                                class="btn-amber px-4 py-2 text-sm">
                            Create Collection
                        </button>
                        <a href="{{ route('admin.collections.index') }}" class="text-sm text-zinc-500 hover:text-zinc-300">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
