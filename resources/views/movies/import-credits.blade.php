<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            Import Credits for {{ $movie->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Upload Form --}}
            <div class="bg-zinc-900 overflow-hidden sm:rounded-lg">
                <div class="p-6 text-zinc-100">

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                            <ul class="text-red-700 text-sm list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-sm text-zinc-400 mb-1">
                        Upload a CSV to bulk-import credits for <strong>{{ $movie->title }}</strong>.
                        <strong>This will replace all existing credits</strong> for this movie.
                    </p>
                    <p class="text-sm text-zinc-400 mb-4">
                        People and credit types that don't exist yet will be created automatically.
                    </p>

                    <div class="mb-5 p-3 bg-zinc-900 border border-zinc-800 rounded-md">
                        <p class="text-xs font-medium text-zinc-500 mb-1">Expected format:</p>
                        <pre class="text-xs font-mono text-zinc-300">person_name,type,character
Keanu Reeves,Actor,Neo
Laurence Fishburne,Actor,Morpheus
Lana Wachowski,Director,
Keanu Reeves,Actor|Director,Neo</pre>
                        <p class="text-xs text-zinc-500 mt-2">Use <code class="bg-zinc-800 px-1 rounded font-mono">|</code> in the <code class="bg-zinc-800 px-1 rounded font-mono">type</code> column to assign multiple credit types for the same person on one row. Character values are consumed in order by <strong>Actor</strong> credits only — other types always have no character.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.movies.credits.import.store', $movie) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="flex items-center gap-4">
                            <input type="file"
                                   name="file"
                                   accept=".csv,.txt"
                                   class="block text-sm text-zinc-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-amber-900/20 file:text-amber-300 hover:file:bg-amber-900/30 cursor-pointer"
                                   required>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-md hover:bg-amber-400 transition">
                                Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Results --}}
            @isset($imported)
                @if($imported > 0)
                    <div class="p-4 bg-green-50 border border-green-200 rounded-md">
                        <p class="text-green-700 text-sm font-medium">
                            ✓ {{ $imported }} {{ Str::plural('credit', $imported) }} imported successfully.
                        </p>
                    </div>
                @endif

                @if(!empty($rowErrors))
                    <div class="bg-zinc-900 overflow-hidden sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-semibold text-zinc-200 mb-3">
                                {{ count($rowErrors) }} {{ Str::plural('row', count($rowErrors)) }} skipped
                            </h3>
                            <table class="w-full text-sm border-collapse">
                                <thead>
                                    <tr class="bg-zinc-800 text-left">
                                        <th class="border border-zinc-800 px-3 py-2 font-medium text-zinc-400">Row</th>
                                        <th class="border border-zinc-800 px-3 py-2 font-medium text-zinc-400">Person</th>
                                        <th class="border border-zinc-800 px-3 py-2 font-medium text-zinc-400">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rowErrors as $rowError)
                                        <tr class="hover:bg-zinc-800">
                                            <td class="border border-zinc-800 px-3 py-2 text-zinc-500">{{ $rowError['row'] }}</td>
                                            <td class="border border-zinc-800 px-3 py-2">{{ $rowError['person'] }}</td>
                                            <td class="border border-zinc-800 px-3 py-2 text-red-600">{{ $rowError['reason'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($imported === 0 && empty($rowErrors))
                    <p class="text-sm text-zinc-500">The file was empty or contained no data rows.</p>
                @endif
            @endisset

            <div>
                <a href="{{ route('admin.movies.edit', $movie) }}" class="text-amber-400 hover:underline text-sm">&larr; Back to {{ $movie->title }}</a>
            </div>

        </div>
    </div>
</x-app-layout>
