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
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Director</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $movie->director }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Release Year</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $movie->release_year }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Cast</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                @if($movie->actors->isEmpty())
                                    <span class="text-gray-400">No cast listed.</span>
                                @else
                                    <ul class="space-y-1">
                                        @foreach($movie->actors as $actor)
                                            <li>
                                                <a href="{{ route('actors.show', $actor) }}" class="text-indigo-600 hover:underline">{{ $actor->name }}</a>
                                                @if($actor->pivot->role)
                                                    <span class="text-gray-500 text-sm">as {{ $actor->pivot->role }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <a href="{{ url()->previous() }}" class="text-indigo-600 hover:underline text-sm">&larr; Back</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
