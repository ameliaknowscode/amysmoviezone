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

                    <dl class="divide-y divide-gray-100 mb-6">
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
                                {{ $person->date_of_birth ? \Carbon\Carbon::parse($person->date_of_birth)->format('F j, Y') : '—' }}
                            </dd>
                        </div>
                        @if($person->date_of_death)
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Date of Death</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                {{ \Carbon\Carbon::parse($person->date_of_death)->format('F j, Y') }}
                            </dd>
                        </div>
                        @endif
                    </dl>

                    <h3 class="text-base font-semibold text-gray-800 mb-3">{{ $type->name }} Credits</h3>

                    @if($personTypes->count() > 1)
                        <select onchange="window.location.href = this.value"
                                class="mb-4 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($personTypes as $t)
                                <option value="{{ route('credits.by-type', [\Illuminate\Support\Str::slug($t->name), $person->slug]) }}"
                                        {{ $t->id === $type->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

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

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
