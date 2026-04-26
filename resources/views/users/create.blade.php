<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">
            {{ __('Create User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-6 text-zinc-100">

                    @if($errors->any())
                        <ul class="mb-4 text-red-600 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-zinc-300 mb-1">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="username" class="block font-medium text-sm text-zinc-300 mb-1">Username</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block font-medium text-sm text-zinc-300 mb-1">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block font-medium text-sm text-zinc-300 mb-1">Password</label>
                            <input type="password" id="password" name="password"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="block font-medium text-sm text-zinc-300 mb-1">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="w-full max-w-md border-zinc-700 rounded-md shadow-sm">
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="px-4 py-2 bg-zinc-800 text-white rounded-md hover:bg-zinc-700">
                                Create User
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="text-zinc-400 hover:underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
