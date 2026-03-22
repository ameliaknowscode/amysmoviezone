<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Ratings Privacy') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Control who can see your ratings and watchlists.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('watchlist.privacy') }}" class="mt-6 space-y-4">
        @csrf
        @method('PATCH')

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-700">Ratings</p>
                <p class="text-xs text-gray-500">Your star ratings and likes</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    name="ratings_private_toggle"
                    value="1"
                    class="sr-only peer"
                    {{ $user->ratings_private ? 'checked' : '' }}
                    onchange="this.form.querySelector('[name=ratings_private]').value = this.checked ? '1' : '0'; this.form.submit();"
                >
                <input type="hidden" name="ratings_private" value="{{ $user->ratings_private ? '1' : '0' }}">
                <input type="hidden" name="want_to_watch_private" value="{{ $user->want_to_watch_private ? '1' : '0' }}">
                <input type="hidden" name="watched_private" value="{{ $user->watched_private ? '1' : '0' }}">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                <span class="ms-3 text-sm text-gray-500">{{ $user->ratings_private ? 'Private' : 'Public' }}</span>
            </label>
        </div>

        @if (session('status') === 'privacy-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600"
            >{{ __('Saved.') }}</p>
        @endif
    </form>
</section>
