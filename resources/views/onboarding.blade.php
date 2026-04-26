<x-app-layout>
    <div class="py-16">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-zinc-900 sm:rounded-lg overflow-hidden">
                {{-- Header band --}}
                <div class="bg-amber-500 px-8 py-10 text-center">
                    <div class="text-4xl mb-3">&#127902;</div>
                    <h1 class="text-2xl font-bold text-white">Welcome to Amy's Movie Zone, {{ auth()->user()->name }}!</h1>
                    <p class="mt-2 text-zinc-300 text-sm">Your personal film diary starts here.</p>
                </div>

                {{-- Steps --}}
                <div class="px-8 py-8 space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 font-semibold text-sm">1</div>
                        <div>
                            <h3 class="font-semibold text-zinc-100">Rate movies you've seen</h3>
                            <p class="text-sm text-zinc-500 mt-0.5">Give films a star rating and unlock personalised recommendations the more you rate.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 font-semibold text-sm">2</div>
                        <div>
                            <h3 class="font-semibold text-zinc-100">Build your watchlist</h3>
                            <p class="text-sm text-zinc-500 mt-0.5">Save films you want to see, log ones you've watched, and write reviews in your diary.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-amber-900/30 flex items-center justify-center text-amber-400 font-semibold text-sm">3</div>
                        <div>
                            <h3 class="font-semibold text-zinc-100">Follow people</h3>
                            <p class="text-sm text-zinc-500 mt-0.5">See what others are watching and rating in your personal activity feed.</p>
                        </div>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="px-8 pb-8">
                    <form method="POST" action="{{ route('onboarding.complete') }}">
                        @csrf
                        <button type="submit"
                            class="w-full bg-amber-500 hover:bg-amber-400 text-white font-semibold py-3 px-6 rounded-lg transition-colors text-sm">
                            Start browsing movies
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
