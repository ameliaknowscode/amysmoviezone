<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $person->name }}
            <span class="text-gray-400 font-normal">— {{ $type->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <ul class="divide-y divide-gray-100">
                        @foreach($credits as $credit)
                            <li class="py-3 flex items-baseline gap-3">
                                <a href="{{ $credit->movie->publicUrl() }}"
                                   class="text-sm font-medium text-indigo-600 hover:underline">
                                    {{ $credit->movie->title }}
                                </a>
                                <span class="text-sm text-gray-400">({{ $credit->movie->release_year }})</span>
                                @if($credit->character)
                                    <span class="text-sm text-gray-500">as {{ $credit->character }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6">
                        <a href="{{ route('people.show', $person) }}"
                           class="text-indigo-600 hover:underline text-sm">&larr; Back to {{ $person->name }}</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
