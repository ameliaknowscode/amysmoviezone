<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            Import Credits
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 overflow-hidden sm:rounded-lg">
                <div class="p-6 text-zinc-100">
                    <p class="text-sm text-zinc-400 mb-4">Select a movie to import credits for.</p>

                    <form
                        x-data="{ movieId: '' }"
                        x-on:submit.prevent="movieId && (window.location = `/admin/movies/${movieId}/credits/import`)"
                    >
                        <div class="flex items-center gap-4">
                            <select
                                x-model="movieId"
                                class="flex-1 border-zinc-700 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">— Select a movie —</option>
                                @foreach ($movies as $movie)
                                    <option value="{{ $movie->id }}">{{ $movie->title }} ({{ $movie->release_year }})</option>
                                @endforeach
                            </select>

                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-md hover:bg-amber-400 transition"
                            >
                                Go
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
