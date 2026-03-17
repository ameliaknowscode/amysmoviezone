<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $person->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <dl class="divide-y divide-gray-100">
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $person->name }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $person->nationality ?? '—' }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                {{ $person->date_of_birth ? \Carbon\Carbon::parse($person->date_of_birth)->format('d M Y') : '—' }}
                            </dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Filmography</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                @if($person->credits->isEmpty())
                                    <span class="text-gray-400">No credits listed.</span>
                                @else
                                    <ul class="space-y-1">
                                        @foreach($person->credits as $credit)
                                            <li>
                                                <a href="{{ route('movies.show', $credit->movie) }}" class="text-indigo-600 hover:underline">{{ $credit->movie->title }}</a>
                                                <span class="text-gray-400">({{ $credit->movie->release_year }})</span>
                                                <span class="text-gray-500 text-sm">— {{ $credit->type->name }}</span>
                                                @if($credit->character)
                                                    <span class="text-gray-500 text-sm">as {{ $credit->character }}</span>
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
