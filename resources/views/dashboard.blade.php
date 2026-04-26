<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 overflow-hidden sm:rounded-lg">
                <div class="p-6 text-zinc-100">
                    {{ __("This is your dashboard!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
